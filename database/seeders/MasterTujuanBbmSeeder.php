<?php

namespace Database\Seeders;

use App\Models\MasterTujuanBbm;
use App\Models\Nagari;
use Illuminate\Database\Seeder;

class MasterTujuanBbmSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil beberapa nagari representatif dari berbagai kecamatan
        $sampleNagaris = Nagari::with('kecamatan')->whereIn('id', [
            1,  // Nagari Muaro (Kec. Sijunjung)
            3,  // Nagari Silokek (Kec. Sijunjung)
            7,  // Nagari Durian Gadang (Kec. Sijunjung)
            10, // Nagari Limo Koto (Kec. Koto VII)
            17, // Nagari Silantai (Kec. Sumpur Kudus)
            21, // Nagari Sumpur Kudus (Kec. Sumpur Kudus)
            28, // Nagari Batu Manjulur (Kec. Kupitan)
            32, // Nagari Palangki (Kec. IV Nagari)
            37, // Nagari Lubuk Tarok (Kec. Lubuk Tarok)
            44, // Nagari Tanjung Gadang (Kec. Tanjung Gadang)
            53, // Nagari Kamang (Kec. Kamang Baru)
            55, // Nagari Aia Amo (Kec. Kamang Baru)
        ])->get();

        foreach ($sampleNagaris as $nagari) {
            MasterTujuanBbm::updateOrCreate(
                [
                    'nagari_id' => $nagari->id,
                    'jenis_kendaraan' => 'roda_4',
                ],
                [
                    'nama_tujuan' => "{$nagari->nama_nagari} (Kec. {$nagari->kecamatan?->nama_kecamatan})",
                    'kecamatan_id' => $nagari->kecamatan_id,
                    'kategori_wilayah' => 'dalam_kabupaten',
                    'jarak_km' => $nagari->jarak_km ?: 25,
                    'kuota_liter' => $nagari->kuota_liter_bbm ?: 20,
                    'keterangan' => "Standar kuota BBM dinas tujuan {$nagari->nama_nagari}",
                    'aktif' => true,
                ]
            );
        }

        // 2. Destinasi Luar Kabupaten & Luar Provinsi
        $luarKabupaten = [
            [
                'nama_tujuan' => 'Kota Padang (Provinsi Sumatera Barat)',
                'kategori_wilayah' => 'luar_kabupaten',
                'jarak_km' => 140,
                'kuota_liter' => 50,
                'jenis_kendaraan' => 'roda_4',
                'keterangan' => 'Koordinasi dinas tingkat provinsi di Ibukota Padang (PP)',
                'aktif' => true,
            ],
            [
                'nama_tujuan' => 'Kota Bukittinggi / Payakumbuh',
                'kategori_wilayah' => 'luar_kabupaten',
                'jarak_km' => 120,
                'kuota_liter' => 45,
                'jenis_kendaraan' => 'roda_4',
                'keterangan' => 'Kunjungan kerja dinas wilayah Bukittinggi/Payakumbuh (PP)',
                'aktif' => true,
            ],
            [
                'nama_tujuan' => 'Kota Pekanbaru (Riau)',
                'kategori_wilayah' => 'luar_provinsi',
                'jarak_km' => 260,
                'kuota_liter' => 90,
                'jenis_kendaraan' => 'roda_4',
                'keterangan' => 'Perjalanan dinas antar provinsi via jalur darat (PP)',
                'aktif' => true,
            ],
        ];

        foreach ($luarKabupaten as $item) {
            MasterTujuanBbm::updateOrCreate(
                [
                    'nama_tujuan' => $item['nama_tujuan'],
                    'jenis_kendaraan' => $item['jenis_kendaraan'],
                ],
                $item
            );
        }
    }
}
