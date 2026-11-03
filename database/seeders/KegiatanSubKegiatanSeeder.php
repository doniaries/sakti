<?php

namespace Database\Seeders;

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

        $data = [
            [
                'kode_kegiatan' => '1.01.02.1.01',
                'nama_kegiatan' => 'Program Penunjang Urusan Pemerintahan Daerah',
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
            [
                'kode_kegiatan' => '1.01.02.1.02',
                'nama_kegiatan' => 'Program Pembinaan dan Pengembangan Olahraga',
                'sub_kegiatan' => [
                    [
                        'kode_sub_kegiatan' => '1.01.02.1.02.0001',
                        'nama_sub_kegiatan' => 'Pembinaan dan Pengembangan Olahragawan Berprestasi',
                    ],
                    [
                        'kode_sub_kegiatan' => '1.01.02.1.02.0002',
                        'nama_sub_kegiatan' => 'Penyelenggaraan Kejuaraan Olahraga Daerah',
                    ],
                ],
            ],
            [
                'kode_kegiatan' => '1.01.02.1.03',
                'nama_kegiatan' => 'Program Pengembangan Kapasitas Kepemudaan',
                'sub_kegiatan' => [
                    [
                        'kode_sub_kegiatan' => '1.01.02.1.03.0001',
                        'nama_sub_kegiatan' => 'Pemberdayaan dan Pengembangan Organisasi Kepemudaan',
                    ],
                ],
            ],
        ];

        foreach ($data as $item) {
            $kegiatan = MasterKegiatan::create([
                'kode_kegiatan' => $item['kode_kegiatan'],
                'nama_kegiatan' => $item['nama_kegiatan'],
            ]);

            foreach ($item['sub_kegiatan'] as $sub) {
                MasterSubKegiatan::create([
                    'kegiatan_id' => $kegiatan->id,
                    'kode_sub_kegiatan' => $sub['kode_sub_kegiatan'],
                    'nama_sub_kegiatan' => $sub['nama_sub_kegiatan'],
                ]);
            }
        }

        $this->command->info('Data Master Kegiatan & Sub Kegiatan berhasil disimpan.');
    }
}

