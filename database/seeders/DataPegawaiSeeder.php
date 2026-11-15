<?php

namespace Database\Seeders;

use App\Enums\StatusKepegawaian;
use App\Models\Jabatan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DataPegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['super_admin', 'admin', 'admin_kepegawaian', 'admin_keuangan', 'pa', 'ppk', 'pptk', 'jf', 'bendahara', 'pegawai'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $defaultPassword = Hash::make('password');
        $superAdminPassword = Hash::make('@Iamsuperadmin');

        // 1. Seed biodata dasar dari pegawai_80.json jika ada
        $jsonPath = database_path('seeders/pegawai_80.json');
        if (file_exists($jsonPath)) {
            $dataRaw = json_decode(file_get_contents($jsonPath), true) ?? [];
            $bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $bulanEng = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

            foreach ($dataRaw as $row) {
                $nama = $row['nama'] ?? '';
                $nip = $row['nip'] ?? null;
                if (blank($nip)) {
                    continue;
                }

                $namaTanpaGelar = trim(explode(',', $nama)[0]);
                $emailPrefix = strtolower(str_replace([' ', '.'], '', $namaTanpaGelar));

                $tanggalLahir = null;
                if (! empty($row['tanggal_lahir'])) {
                    $tanggalString = str_replace($bulanIndo, $bulanEng, $row['tanggal_lahir']);
                    try {
                        $tanggalLahir = Carbon::parse($tanggalString)->format('Y-m-d');
                    } catch (\Exception) {
                        $tanggalLahir = null;
                    }
                }

                $jabatanId = null;
                if (! empty($row['jabatan'])) {
                    $jabatanId = Jabatan::query()->whereRaw('LOWER(nama) = ?', [strtolower($row['jabatan'])])->value('id');
                }

                $item = [
                    'name' => $nama,
                    'nip' => $nip,
                    'status_kepegawaian' => StatusKepegawaian::detectFromNip($nip)->value,
                    'jabatan_id' => $jabatanId,
                    'pendidikan' => $row['pendidikan'] ?? null,
                    'jenis_kelamin' => $row['jk'] ?? null,
                    'eselon' => $row['eselon'] ?? null,
                    'tempat_lahir' => $row['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $tanggalLahir,
                    'email' => $emailPrefix . '@sijunjung.go.id',
                    'password' => $defaultPassword,
                ];

                $user = User::updateOrCreate(['nip' => $nip], $item);
                if (blank($user->nomor_absensi)) {
                    $user->nomor_absensi = (string) $user->id;
                    $user->save();
                }
                $user->assignRole('pegawai');
            }
        }

        // 2. Sinkronkan data pegawai, email resmi, jabatan, gelar, dan role dari penempatan_pegawai.json
        $penempatanPath = database_path('seeders/penempatan_pegawai.json');
        if (! file_exists($penempatanPath)) {
            $this->command?->warn('File penempatan_pegawai.json tidak ditemukan.');
            return;
        }

        $penempatan = json_decode(file_get_contents($penempatanPath), true);
        foreach ($penempatan['users'] ?? [] as $row) {
            $email = $row['email'] ?? null;
            $nip = $row['nip'] ?? null;
            if (blank($email)) {
                continue;
            }

            $user = filled($nip)
                ? User::query()->where('nip', $nip)->first()
                : User::query()->where('email', $email)->first();

            $jabatanId = ! empty($row['jabatan'])
                ? Jabatan::query()->whereRaw('LOWER(nama) = ?', [strtolower($row['jabatan'])])->value('id')
                : null;

            $isTechnicalAdmin = in_array($email, ['superadmin@gmail.com', 'doniaries@gmail.com'], true);

            $payload = [
                'name' => $row['name'],
                'gelar_depan' => $row['gelar_depan'] ?? null,
                'gelar_belakang' => $row['gelar_belakang'] ?? null,
                'nip' => $nip,
                'status_kepegawaian' => $row['status_kepegawaian'] ?? ($isTechnicalAdmin ? StatusKepegawaian::PNS->value : StatusKepegawaian::detectFromNip($nip)->value),
                'email' => $email,
                'jabatan_id' => $jabatanId,
                'anggaran_role' => $row['anggaran_role'] ?? null,
                'status_kerja' => $row['status_kerja'] ?? 'wfo',
                'kuota_cuti' => $row['kuota_cuti'] ?? 12,
                'aktif' => $row['aktif'] ?? true,
                'nomor_absensi' => $row['nomor_absensi'] ?? null,
            ];

            if ($email === 'superadmin@gmail.com') {
                $payload['bidang_id'] = null;
                $payload['atasan_id'] = null;
            }

            if ($user) {
                $user->update($payload);
            } else {
                $payload['password'] = $isTechnicalAdmin ? $superAdminPassword : $defaultPassword;
                $user = User::query()->create($payload);
            }

            if (! empty($row['roles'])) {
                $user->syncRoles($row['roles']);
            }
        }
    }
}
