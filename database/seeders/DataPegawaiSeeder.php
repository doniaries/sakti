<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DataPegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/pegawai_80.json');
        if (!file_exists($jsonPath)) {
            $this->command->warn('File pegawai_80.json tidak ditemukan!');
            return;
        }

        $dataRaw = json_decode(file_get_contents($jsonPath), true);

        $data = [];
        $defaultPassword = \Illuminate\Support\Facades\Hash::make('password');
        
        foreach ($dataRaw as $row) {
            $nama = $row['nama'] ?? '';
            $namaTanpaGelar = trim(explode(',', $nama)[0]);
            $emailPrefix = strtolower(str_replace([' ', '.'], '', $namaTanpaGelar));
            
            $data[] = [
                'name' => $nama,
                'nip' => $row['nip'] ?? null,
                'jabatan' => $row['jabatan'] ?? null,
                'pendidikan' => $row['pendidikan'] ?? null,
                'jenis_kelamin' => $row['jk'] ?? null,
                'eselon' => $row['eselon'] ?? null,
                'tempat_lahir' => $row['tempat_lahir'] ?? null,
                'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
                'nomor_absensi' => $row['nomor_absensi'] ?? null,
                'email' => $emailPrefix . '@sijunjung.go.id',
                'password' => $defaultPassword,
            ];
        }

        $bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $bulanEng = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        foreach ($data as $item) {
            $jabatanName = $item['jabatan'] ?? null;
            unset($item['jabatan']);
            if ($jabatanName) {
                $jabatan = \App\Models\Jabatan::where('nama', $jabatanName)->first();
                if ($jabatan) {
                    $item['jabatan_id'] = $jabatan->id;
                }
            }

            if (!empty($item['tanggal_lahir'])) {
                $tanggalString = str_replace($bulanIndo, $bulanEng, $item['tanggal_lahir']);
                try {
                    $item['tanggal_lahir'] = \Carbon\Carbon::parse($tanggalString)->format('Y-m-d');
                } catch (\Exception $e) {
                    $item['tanggal_lahir'] = null;
                }
            }

            $rawNama = $item['nama'] ?? '';
            if (str_contains($rawNama, ',')) {
                $parts = explode(',', $rawNama);
                array_shift($parts);
                $item['gelar_belakang'] = trim(implode(', ', array_map('trim', $parts)));
            }
            if (preg_match('/^(Dr\.|Dra\.|Drs\.|Ir\.|Prof\.|H\.|Hj\.|Apt\.)\s+/i', $rawNama, $m)) {
                $item['gelar_depan'] = trim($m[1]);
            }

            $user = User::updateOrCreate(
                ['nip' => $item['nip']],
                $item
            );
            $user->nomor_absensi = (string)$user->id;
            $user->save();
            $user->assignRole('pegawai');
        }

        // --- Atur Akun Khusus & Peran Anggaran (Enum AnggaranRole) ---
        // 1. PPTK Pemasaran Pariwisata (DONI ARIES) - bukan lagi SuperAdmin
        $doni = User::query()->where('name', 'LIKE', '%DONI ARIES%')->first();
        if ($doni) {
            $doni->update([
                'email' => 'doniaries@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('@Iamsuperadmin'),
                'aktif' => true,
                'anggaran_role' => \App\Enums\AnggaranRole::PPTK,
            ]);
            $doni->syncRoles(['pegawai']);
        }

        // 2. Akun Khusus SuperAdmin Sistem
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'SUPER ADMIN SISTEM',
                'nip' => '00000000 000000 0 001',
                'password' => \Illuminate\Support\Facades\Hash::make('@Iamsuperadmin'),
                'aktif' => true,
                'anggaran_role' => null,
                'bidang_id' => null,
            ]
        );
        $superAdmin->syncRoles(['super_admin']);

        // 3. Admin Dinas / Kasubag Umum (ZULFITRI)
        $zulfitri = User::query()->where('name', 'LIKE', '%ZULFITRI%')->first();
        if ($zulfitri) {
            $zulfitri->update([
                'email' => 'admin@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('admin'),
                'aktif' => true,
                'anggaran_role' => null,
            ]);
            $zulfitri->syncRoles(['admin', 'pegawai']);
        }

        // 4. Kepala Dinas / PA - Pengguna Anggaran (AFRINELDI)
        $afrineldi = User::query()->where('name', 'LIKE', '%AFRINELDI%')->first();
        if ($afrineldi) {
            $afrineldi->update([
                'email' => 'kepaladinas@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'aktif' => true,
                'anggaran_role' => \App\Enums\AnggaranRole::PA,
            ]);
            $afrineldi->syncRoles(['pegawai']);
        }

        // 5. PPK - Pejabat Pembuat Komitmen (JULHARDIANTO)
        $julhardianto = User::query()->where('name', 'LIKE', '%JULHARDIANTO%')->first();
        if ($julhardianto) {
            $julhardianto->update([
                'email' => 'julhardianto@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'aktif' => true,
                'anggaran_role' => \App\Enums\AnggaranRole::PPK,
            ]);
            $julhardianto->syncRoles(['pegawai']);
        }

        // 6. Sekretaris / PPTK Sekretariat (DESMAWATI)
        $desmawati = User::query()->where('name', 'LIKE', '%DESMAWATI%')->first();
        if ($desmawati) {
            $desmawati->update([
                'email' => 'sekretaris@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'aktif' => true,
                'anggaran_role' => \App\Enums\AnggaranRole::PPTK,
            ]);
            $desmawati->syncRoles(['pegawai']);
        }

        // 7. Kabid Destinasi / PPTK Destinasi (WILDA ARDES)
        $wilda = User::query()->where('name', 'LIKE', '%WILDA ARDES%')->first();
        if ($wilda) {
            $wilda->update([
                'email' => 'wilda@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'aktif' => true,
                'anggaran_role' => \App\Enums\AnggaranRole::PPTK,
            ]);
            $wilda->syncRoles(['pegawai']);
        }

        // 8. Kabid Pemuda & Olahraga / PPTK Dispora (INDRAWADI)
        $indrawadi = User::query()->where('name', 'LIKE', '%INDRAWADI%')->first();
        if ($indrawadi) {
            $indrawadi->update([
                'email' => 'kabid@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'aktif' => true,
                'anggaran_role' => \App\Enums\AnggaranRole::PPTK,
            ]);
            $indrawadi->syncRoles(['pegawai']);
        }

        // 9. 2 Orang Jabatan Fungsional (JF) untuk Akses Cepat
        $diki = User::query()->where('name', 'LIKE', '%DIKI ZULFIKAR%')->first();
        if ($diki) {
            $diki->update([
                'email' => 'dikizulfikar@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'aktif' => true,
                'anggaran_role' => null,
            ]);
            $diki->syncRoles(['pegawai']);
        }

        $andra = User::query()->where('name', 'LIKE', '%ANDRA PEBTALIYUS%')->first();
        if ($andra) {
            $andra->update([
                'email' => 'andrapebtaliyus@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'aktif' => true,
                'anggaran_role' => null,
            ]);
            $andra->syncRoles(['pegawai']);
        }

        // 10. 2 Orang Staf Pelaksana untuk Akses Cepat
        $maulana = User::query()->where('name', 'LIKE', '%MAULANA IBRAHIM%')->first();
        if ($maulana) {
            $maulana->update([
                'email' => 'maulanaibrahim@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'aktif' => true,
                'anggaran_role' => null,
            ]);
            $maulana->syncRoles(['pegawai']);
        }

        $roni = User::query()->where('name', 'LIKE', '%RONI RIVANDRA%')->first();
        if ($roni) {
            $roni->update([
                'email' => 'ronirivandra@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'aktif' => true,
                'anggaran_role' => null,
            ]);
            $roni->syncRoles(['pegawai']);
        }
    }
}
