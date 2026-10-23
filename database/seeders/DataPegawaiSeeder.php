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

        // --- Atur Akun Khusus ---
        // 1. Superadmin (DONI ARIES)
        $doni = User::query()->where('name', 'LIKE', '%DONI ARIES%', 'and')->first();
        if ($doni) {
            $doni->update([
                'email' => 'superadmin@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('@Iamsuperadmin'),
                'aktif' => true,
            ]);
            $doni->syncRoles(['super_admin', 'pegawai']);
        }

        // 2. Admin / Kasubag Umum (ZULFITRI)
        $zulfitri = User::query()->where('name', 'LIKE', '%ZULFITRI%', 'and')->first();
        if ($zulfitri) {
            $zulfitri->update([
                'email' => 'admin@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('admin'),
                'aktif' => true,
            ]);
            $zulfitri->syncRoles(['admin', 'pegawai']);
        }

        // 3. Kepala Dinas (AFRINELDI)
        $afrineldi = User::query()->where('name', 'LIKE', '%AFRINELDI%', 'and')->first();
        if ($afrineldi) {
            $afrineldi->update([
                'email' => 'kepaladinas@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'aktif' => true,
            ]);
            $afrineldi->syncRoles(['pegawai']);
        }

        // 4. Sekretaris (DESMIWARTI)
        $desmiwarti = User::query()->where('name', 'LIKE', '%DESMIWARTI%', 'and')->first();
        if ($desmiwarti) {
            $desmiwarti->update([
                'email' => 'sekretaris@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'aktif' => true,
            ]);
            $desmiwarti->syncRoles(['pegawai']);
        }

        // 5. Kepala Bidang (INDRAWADI)
        $indrawadi = User::query()->where('name', 'LIKE', '%INDRAWADI%', 'and')->first();
        if ($indrawadi) {
            $indrawadi->update([
                'email' => 'kabid@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'aktif' => true,
            ]);
            $indrawadi->syncRoles(['pegawai']);
        }

        // 6. Pegawai (WILDA ARDES)
        $wilda = User::query()->where('name', 'LIKE', '%WILDA ARDES%', 'and')->first();
        if ($wilda) {
            $wilda->update([
                'email' => 'wilda@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'aktif' => true,
            ]);
            $wilda->syncRoles(['pegawai']);
        }
    }
}
