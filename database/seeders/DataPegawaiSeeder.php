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
        foreach ($dataRaw as $row) {
            $data[] = [
                'name' => $row['nama'] ?? null,
                'nip' => $row['nip'] ?? null,
                'jabatan' => $row['jabatan'] ?? null,
                'pendidikan' => $row['pendidikan'] ?? null,
                'jenis_kelamin' => $row['jk'] ?? null,
                'eselon' => $row['eselon'] ?? null,
                'tempat_lahir' => $row['tempat_lahir'] ?? null,
                'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
                'nomor_absensi' => $row['nomor_absensi'] ?? null,
                'email' => strtolower(str_replace([' ', ',', '.'], '', $row['nama'] ?? '')) . '@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
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

            $user = User::updateOrCreate(
                ['nip' => $item['nip']],
                $item
            );
            $user->nomor_absensi = (string)$user->id;
            $user->save();
            $user->assignRole('pegawai');
        }

        // --- Atur Akun Khusus ---
        $doni = User::query()->where('name', 'LIKE', '%DONI ARIES%', 'and')->first();
        if ($doni) {
            $doni->update([
                'email' => 'superadmin@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('@Iamsuperadmin'),
                'aktif' => true,
            ]);
            $doni->syncRoles(['super_admin', 'pegawai']);
        }

        $zulfitri = User::query()->where('name', 'LIKE', '%ZULFITRI%', 'and')->first();
        if ($zulfitri) {
            $zulfitri->update([
                'email' => 'admin@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('admin'),
                'aktif' => true,
            ]);
            $zulfitri->syncRoles(['admin', 'pegawai']);
        }
    }
}
