<?php

namespace Database\Seeders;

use App\Models\KodeRekening;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KodeRekeningSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        KodeRekening::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $kodeRekeningData = [
            [
                'akun' => '1',
                'kelompok' => '1',
                'jenis' => '7',
                'objek' => '01',
                'rincian_objek' => '01',
                'sub_rincian_objek' => '01',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '1.1.7.01.01.01.000',
                'uraian' => 'ALAT TULIS KANTOR'
            ],
            [
                'akun' => '1',
                'kelompok' => '1',
                'jenis' => '7',
                'objek' => '01',
                'rincian_objek' => '02',
                'sub_rincian_objek' => '01',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '1.1.7.01.02.01.000',
                'uraian' => 'PERALATAN KOMPUTER'
            ],
            [
                'akun' => '1',
                'kelompok' => '1',
                'jenis' => '7',
                'objek' => '01',
                'rincian_objek' => '03',
                'sub_rincian_objek' => '01',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '1.1.7.01.03.01.000',
                'uraian' => 'PERLENGKAPAN KANTOR'
            ],
            [
                'akun' => '1',
                'kelompok' => '1',
                'jenis' => '7',
                'objek' => '01',
                'rincian_objek' => '04',
                'sub_rincian_objek' => '01',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '1.1.7.01.04.01.000',
                'uraian' => 'BAHAN CETAKAN'
            ]
        ];

        foreach ($kodeRekeningData as $data) {
            KodeRekening::create($data);
        }

        $this->command->info('Kode Rekening berhasil disimpan.');
    }
}

