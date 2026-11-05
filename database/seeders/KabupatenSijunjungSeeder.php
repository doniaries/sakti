<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use App\Models\Nagari;
use Illuminate\Database\Seeder;

class KabupatenSijunjungSeeder extends Seeder
{
    public function run(): void
    {
        $kecamatans = [
            1 => 'Sijunjung',
            2 => 'Koto VII',
            3 => 'Sumpur Kudus',
            4 => 'Kupitan',
            5 => 'IV Nagari',
            6 => 'Lubuk Tarok',
            7 => 'Tanjung Gadang',
            8 => 'Kamang Baru',
        ];

        foreach ($kecamatans as $id => $nama) {
            Kecamatan::updateOrCreate(
                ['id' => $id],
                ['nama_kecamatan' => $nama]
            );
        }

        $nagaris = [
            // Kec. Sijunjung (1)
            ['id' => 1, 'kecamatan_id' => 1, 'nama_nagari' => 'Nagari Muaro', 'jarak_km' => 5, 'kuota_liter_bbm' => 10],
            ['id' => 2, 'kecamatan_id' => 1, 'nama_nagari' => 'Nagari Kandang Baru', 'jarak_km' => 8, 'kuota_liter_bbm' => 12],
            ['id' => 3, 'kecamatan_id' => 1, 'nama_nagari' => 'Nagari Silokek', 'jarak_km' => 18, 'kuota_liter_bbm' => 15],
            ['id' => 4, 'kecamatan_id' => 1, 'nama_nagari' => 'Nagari Pematang Panjang', 'jarak_km' => 12, 'kuota_liter_bbm' => 12],
            ['id' => 5, 'kecamatan_id' => 1, 'nama_nagari' => 'Nagari Solok Ambah', 'jarak_km' => 35, 'kuota_liter_bbm' => 22],
            ['id' => 6, 'kecamatan_id' => 1, 'nama_nagari' => 'Nagari Paru', 'jarak_km' => 25, 'kuota_liter_bbm' => 18],
            ['id' => 7, 'kecamatan_id' => 1, 'nama_nagari' => 'Nagari Durian Gadang', 'jarak_km' => 28, 'kuota_liter_bbm' => 20],
            ['id' => 8, 'kecamatan_id' => 1, 'nama_nagari' => 'Nagari Aie Angek', 'jarak_km' => 15, 'kuota_liter_bbm' => 15],
            ['id' => 9, 'kecamatan_id' => 1, 'nama_nagari' => 'Nagari Sijunjung', 'jarak_km' => 7, 'kuota_liter_bbm' => 10],

            // Kec. Koto VII (2)
            ['id' => 10, 'kecamatan_id' => 2, 'nama_nagari' => 'Nagari Limo Koto', 'jarak_km' => 18, 'kuota_liter_bbm' => 15],
            ['id' => 11, 'kecamatan_id' => 2, 'nama_nagari' => 'Nagari Palaluar', 'jarak_km' => 22, 'kuota_liter_bbm' => 18],
            ['id' => 12, 'kecamatan_id' => 2, 'nama_nagari' => 'Nagari Guguk', 'jarak_km' => 20, 'kuota_liter_bbm' => 16],
            ['id' => 13, 'kecamatan_id' => 2, 'nama_nagari' => 'Nagari Padang Laweh', 'jarak_km' => 25, 'kuota_liter_bbm' => 18],
            ['id' => 14, 'kecamatan_id' => 2, 'nama_nagari' => 'Nagari Tanjung', 'jarak_km' => 24, 'kuota_liter_bbm' => 18],
            ['id' => 15, 'kecamatan_id' => 2, 'nama_nagari' => 'Nagari Padang Laweh Selatan', 'jarak_km' => 27, 'kuota_liter_bbm' => 20],
            ['id' => 16, 'kecamatan_id' => 2, 'nama_nagari' => 'Nagari Bukit Bual', 'jarak_km' => 30, 'kuota_liter_bbm' => 22],

            // Kec. Sumpur Kudus (3)
            ['id' => 17, 'kecamatan_id' => 3, 'nama_nagari' => 'Nagari Silantai', 'jarak_km' => 65, 'kuota_liter_bbm' => 35],
            ['id' => 18, 'kecamatan_id' => 3, 'nama_nagari' => 'Nagari Sisawah', 'jarak_km' => 70, 'kuota_liter_bbm' => 38],
            ['id' => 19, 'kecamatan_id' => 3, 'nama_nagari' => 'Nagari Unggan', 'jarak_km' => 75, 'kuota_liter_bbm' => 40],
            ['id' => 20, 'kecamatan_id' => 3, 'nama_nagari' => 'Nagari Tanjung Bonai Aur', 'jarak_km' => 50, 'kuota_liter_bbm' => 30],
            ['id' => 21, 'kecamatan_id' => 3, 'nama_nagari' => 'Nagari Sumpur Kudus', 'jarak_km' => 55, 'kuota_liter_bbm' => 32],
            ['id' => 22, 'kecamatan_id' => 3, 'nama_nagari' => 'Nagari Tamparungo', 'jarak_km' => 58, 'kuota_liter_bbm' => 34],
            ['id' => 23, 'kecamatan_id' => 3, 'nama_nagari' => 'Nagari Kumanis', 'jarak_km' => 45, 'kuota_liter_bbm' => 28],
            ['id' => 24, 'kecamatan_id' => 3, 'nama_nagari' => 'Nagari Mangganti', 'jarak_km' => 60, 'kuota_liter_bbm' => 35],
            ['id' => 25, 'kecamatan_id' => 3, 'nama_nagari' => 'Nagari Sumpur Kudus Selatan', 'jarak_km' => 56, 'kuota_liter_bbm' => 32],
            ['id' => 26, 'kecamatan_id' => 3, 'nama_nagari' => 'Tanjung Labuh', 'jarak_km' => 52, 'kuota_liter_bbm' => 30],
            ['id' => 27, 'kecamatan_id' => 3, 'nama_nagari' => 'TBA Selatan', 'jarak_km' => 53, 'kuota_liter_bbm' => 30],

            // Kec. Kupitan (4)
            ['id' => 28, 'kecamatan_id' => 4, 'nama_nagari' => 'Nagari Batu Manjulur', 'jarak_km' => 28, 'kuota_liter_bbm' => 20],
            ['id' => 29, 'kecamatan_id' => 4, 'nama_nagari' => 'Nagari Pamuatan', 'jarak_km' => 25, 'kuota_liter_bbm' => 18],
            ['id' => 30, 'kecamatan_id' => 4, 'nama_nagari' => 'Nagari Padang Sibusuk', 'jarak_km' => 32, 'kuota_liter_bbm' => 22],
            ['id' => 31, 'kecamatan_id' => 4, 'nama_nagari' => 'Desa Kampung Baru', 'jarak_km' => 26, 'kuota_liter_bbm' => 18],

            // Kec. IV Nagari (5)
            ['id' => 32, 'kecamatan_id' => 5, 'nama_nagari' => 'Nagari Palangki', 'jarak_km' => 15, 'kuota_liter_bbm' => 15],
            ['id' => 33, 'kecamatan_id' => 5, 'nama_nagari' => 'Nagari Koto Baru', 'jarak_km' => 18, 'kuota_liter_bbm' => 16],
            ['id' => 34, 'kecamatan_id' => 5, 'nama_nagari' => 'Nagari Muaro Bodi', 'jarak_km' => 12, 'kuota_liter_bbm' => 14],
            ['id' => 35, 'kecamatan_id' => 5, 'nama_nagari' => 'Nagari Mundam Sakti', 'jarak_km' => 22, 'kuota_liter_bbm' => 18],
            ['id' => 36, 'kecamatan_id' => 5, 'nama_nagari' => 'Nagari Koto Tuo', 'jarak_km' => 20, 'kuota_liter_bbm' => 16],

            // Kec. Lubuk Tarok (6)
            ['id' => 37, 'kecamatan_id' => 6, 'nama_nagari' => 'Nagari Lubuk Tarok', 'jarak_km' => 25, 'kuota_liter_bbm' => 18],
            ['id' => 38, 'kecamatan_id' => 6, 'nama_nagari' => 'Nagari Lalan', 'jarak_km' => 30, 'kuota_liter_bbm' => 22],
            ['id' => 39, 'kecamatan_id' => 6, 'nama_nagari' => 'Nagari Buluh Kasok', 'jarak_km' => 38, 'kuota_liter_bbm' => 25],
            ['id' => 40, 'kecamatan_id' => 6, 'nama_nagari' => 'Nagari Kampung Dalam', 'jarak_km' => 27, 'kuota_liter_bbm' => 20],
            ['id' => 41, 'kecamatan_id' => 6, 'nama_nagari' => 'Nagari Latang', 'jarak_km' => 35, 'kuota_liter_bbm' => 24],
            ['id' => 42, 'kecamatan_id' => 6, 'nama_nagari' => 'Nagari Silongo', 'jarak_km' => 32, 'kuota_liter_bbm' => 22],

            // Kec. Tanjung Gadang (7)
            ['id' => 43, 'kecamatan_id' => 7, 'nama_nagari' => 'Nagari Timbulun', 'jarak_km' => 45, 'kuota_liter_bbm' => 28],
            ['id' => 44, 'kecamatan_id' => 7, 'nama_nagari' => 'Nagari Tanjung Gadang', 'jarak_km' => 42, 'kuota_liter_bbm' => 26],
            ['id' => 45, 'kecamatan_id' => 7, 'nama_nagari' => 'Nagari Taratak Baru', 'jarak_km' => 48, 'kuota_liter_bbm' => 30],
            ['id' => 46, 'kecamatan_id' => 7, 'nama_nagari' => 'Nagari Pulasan', 'jarak_km' => 52, 'kuota_liter_bbm' => 32],
            ['id' => 47, 'kecamatan_id' => 7, 'nama_nagari' => 'Nagari Langki', 'jarak_km' => 60, 'kuota_liter_bbm' => 35],
            ['id' => 48, 'kecamatan_id' => 7, 'nama_nagari' => 'Nagari Sibakur', 'jarak_km' => 55, 'kuota_liter_bbm' => 34],
            ['id' => 49, 'kecamatan_id' => 7, 'nama_nagari' => 'Nagari Tanjung Lolo', 'jarak_km' => 40, 'kuota_liter_bbm' => 25],
            ['id' => 50, 'kecamatan_id' => 7, 'nama_nagari' => 'Nagari Taratak Baru Utara', 'jarak_km' => 50, 'kuota_liter_bbm' => 30],
            ['id' => 51, 'kecamatan_id' => 7, 'nama_nagari' => 'Nagari Sinyamu', 'jarak_km' => 62, 'kuota_liter_bbm' => 36],

            // Kec. Kamang Baru (8)
            ['id' => 52, 'kecamatan_id' => 8, 'nama_nagari' => 'Nagari Sungai Lansek', 'jarak_km' => 85, 'kuota_liter_bbm' => 45],
            ['id' => 53, 'kecamatan_id' => 8, 'nama_nagari' => 'Nagari Kamang', 'jarak_km' => 90, 'kuota_liter_bbm' => 48],
            ['id' => 54, 'kecamatan_id' => 8, 'nama_nagari' => 'Nagari Muaro Takuang', 'jarak_km' => 95, 'kuota_liter_bbm' => 50],
            ['id' => 55, 'kecamatan_id' => 8, 'nama_nagari' => 'Nagari Aia Amo', 'jarak_km' => 110, 'kuota_liter_bbm' => 55],
            ['id' => 56, 'kecamatan_id' => 8, 'nama_nagari' => 'Nagari Sungai Batuang', 'jarak_km' => 88, 'kuota_liter_bbm' => 46],
            ['id' => 57, 'kecamatan_id' => 8, 'nama_nagari' => 'Nagari Kunangan Parit Rantang', 'jarak_km' => 100, 'kuota_liter_bbm' => 52],
            ['id' => 58, 'kecamatan_id' => 8, 'nama_nagari' => 'Nagari Tanjuang Kaliang', 'jarak_km' => 92, 'kuota_liter_bbm' => 48],
            ['id' => 59, 'kecamatan_id' => 8, 'nama_nagari' => 'Nagari Padang Tarok', 'jarak_km' => 96, 'kuota_liter_bbm' => 50],
            ['id' => 60, 'kecamatan_id' => 8, 'nama_nagari' => 'Nagari Siaur', 'jarak_km' => 105, 'kuota_liter_bbm' => 54],
            ['id' => 61, 'kecamatan_id' => 8, 'nama_nagari' => 'Nagari Lubuk Tarantang', 'jarak_km' => 102, 'kuota_liter_bbm' => 52],
            ['id' => 62, 'kecamatan_id' => 8, 'nama_nagari' => 'Nagari Maloro', 'jarak_km' => 98, 'kuota_liter_bbm' => 50],
        ];

        foreach ($nagaris as $n) {
            Nagari::updateOrCreate(
                ['id' => $n['id']],
                [
                    'kecamatan_id' => $n['kecamatan_id'],
                    'nama_nagari' => $n['nama_nagari'],
                    'jarak_km' => $n['jarak_km'],
                    'kuota_liter_bbm' => $n['kuota_liter_bbm'],
                ]
            );
        }
    }
}

