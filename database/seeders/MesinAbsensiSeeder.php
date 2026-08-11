<?php

namespace Database\Seeders;

use App\Models\MesinAbsensi;
use Illuminate\Database\Seeder;

class MesinAbsensiSeeder extends Seeder
{
    public function run(): void
    {
        MesinAbsensi::updateOrCreate(
            [
                'alamat_ip' => '192.168.1.201',
                'port' => 4370,
            ],
            [
                'nama' => 'Mesin Absensi Kantor',
                'merek' => 'ZKTeco',
                'model' => 'uFace302',
                'nomor_seri' => null,
                'jenis_koneksi' => 'tcp_ip',
                'aktif' => true,
                'sinkronisasi_terakhir' => null,
            ]
        );
    }
}
