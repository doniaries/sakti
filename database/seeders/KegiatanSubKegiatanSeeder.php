<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\MasterKegiatan;
use App\Models\MasterSubKegiatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KegiatanSubKegiatanSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterSubKegiatan::truncate();
        MasterKegiatan::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $sekretariat = Bidang::where('nama', 'LIKE', '%Sekretariat%')->first();
        $pemasaran = Bidang::where('nama', 'LIKE', '%Pemasaran%')->first();
        $destinasi = Bidang::where('nama', 'LIKE', '%Destinasi%')->first();
        $pora = Bidang::where('nama', 'LIKE', '%Pemuda%')->first();

        $data = [
            // 1. Bidang Sekretariat
            [
                'bidang_id' => $sekretariat?->id ?? 1,
                'kode_kegiatan' => '1.01.02.1.01',
                'nama_kegiatan' => 'Program Penunjang Urusan Pemerintahan Daerah (Sekretariat)',
                'pagu_anggaran' => 175000000,
                'sub_kegiatan' => [
                    [
                        'kode_sub_kegiatan' => '1.01.02.1.01.0001',
                        'nama_sub_kegiatan' => 'Penyediaan Komponen Instalasi Listrik/Penerangan Bangunan Kantor',
                    ],
                    [
                        'kode_sub_kegiatan' => '1.01.02.1.01.0002',
                        'nama_sub_kegiatan' => 'Penyediaan Peralatan dan Perlengkapan Kantor',
                    ],
                    [
                        'kode_sub_kegiatan' => '1.01.02.1.01.0003',
                        'nama_sub_kegiatan' => 'Penyediaan Bahan Logistik Kantor dan ATK',
                    ],
                    [
                        'kode_sub_kegiatan' => '1.01.02.1.01.0004',
                        'nama_sub_kegiatan' => 'Penyediaan Barang Cetakan dan Penggandaan',
                    ],
                ],
            ],
            // 2. Bidang Pemasaran Pariwisata
            [
                'bidang_id' => $pemasaran?->id ?? 2,
                'kode_kegiatan' => '3.26.03.2.01',
                'nama_kegiatan' => 'Program Pemasaran Pariwisata Daerah',
                'pagu_anggaran' => 120000000,
                'sub_kegiatan' => [
                    [
                        'kode_sub_kegiatan' => '3.26.03.2.01.0001',
                        'nama_sub_kegiatan' => 'Pemasaran Pariwisata Dalam dan Luar Negeri Daya Tarik, Destinasi dan Kawasan Strategis',
                    ],
                    [
                        'kode_sub_kegiatan' => '3.26.03.2.01.0002',
                        'nama_sub_kegiatan' => 'Fasilitasi Kegiatan Pemasaran Pariwisata dan Promosi Event Daerah',
                    ],
                ],
            ],
            // 3. Bidang Pengembangan Destinasi Pariwisata
            [
                'bidang_id' => $destinasi?->id ?? 3,
                'kode_kegiatan' => '3.26.02.2.01',
                'nama_kegiatan' => 'Program Peningkatan Daya Tarik Destinasi Pariwisata',
                'pagu_anggaran' => 140000000,
                'sub_kegiatan' => [
                    [
                        'kode_sub_kegiatan' => '3.26.02.2.01.0001',
                        'nama_sub_kegiatan' => 'Pengelolaan Daya Tarik Wisata Kabupaten/Kota',
                    ],
                    [
                        'kode_sub_kegiatan' => '3.26.02.2.01.0002',
                        'nama_sub_kegiatan' => 'Pengembangan Kawasan Strategis Pariwisata Daerah',
                    ],
                ],
            ],
            // 4. Bidang Pemuda dan Olahraga
            [
                'bidang_id' => $pora?->id ?? 4,
                'kode_kegiatan' => '2.19.02.2.01',
                'nama_kegiatan' => 'Program Pengembangan Kapasitas Daya Saing Kepemudaan',
                'pagu_anggaran' => 85000000,
                'sub_kegiatan' => [
                    [
                        'kode_sub_kegiatan' => '2.19.02.2.01.0001',
                        'nama_sub_kegiatan' => 'Pemberdayaan dan Pengembangan Organisasi Kepemudaan',
                    ],
                ],
            ],
            [
                'bidang_id' => $pora?->id ?? 4,
                'kode_kegiatan' => '2.19.03.2.01',
                'nama_kegiatan' => 'Program Pembinaan dan Pengembangan Olahraga',
                'pagu_anggaran' => 95000000,
                'sub_kegiatan' => [
                    [
                        'kode_sub_kegiatan' => '2.19.03.2.01.0001',
                        'nama_sub_kegiatan' => 'Pembinaan dan Pengembangan Olahragawan Berprestasi',
                    ],
                    [
                        'kode_sub_kegiatan' => '2.19.03.2.01.0002',
                        'nama_sub_kegiatan' => 'Penyelenggaraan Kejuaraan Olahraga Daerah',
                    ],
                ],
            ],
        ];

        foreach ($data as $item) {
            $kegiatan = MasterKegiatan::create([
                'bidang_id' => $item['bidang_id'],
                'kode_kegiatan' => $item['kode_kegiatan'],
                'nama_kegiatan' => $item['nama_kegiatan'],
                'pagu_anggaran' => $item['pagu_anggaran'] ?? 0,
            ]);

            foreach ($item['sub_kegiatan'] as $sub) {
                MasterSubKegiatan::create([
                    'kegiatan_id' => $kegiatan->id,
                    'kode_sub_kegiatan' => $sub['kode_sub_kegiatan'],
                    'nama_sub_kegiatan' => $sub['nama_sub_kegiatan'],
                ]);
            }
        }

        $this->command->info('Data Master Kegiatan & Sub Kegiatan per Bidang berhasil disimpan.');
    }
}
