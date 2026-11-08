<?php

namespace Database\Seeders;

use App\Models\KodeRekening;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KodeRekeningSeeder extends Seeder
{
    public function run(): void
    {
        $kodeRekeningData = [
            [
                'akun' => '5',
                'kelompok' => '1',
                'jenis' => '02',
                'objek' => '01',
                'rincian_objek' => '001',
                'sub_rincian_objek' => '00024',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '5.1.02.01.001.00024',
                'uraian' => 'Belanja Alat/Bahan untuk Kegiatan Kantor-Alat Tulis Kantor',
            ],
            [
                'akun' => '5',
                'kelompok' => '1',
                'jenis' => '02',
                'objek' => '01',
                'rincian_objek' => '001',
                'sub_rincian_objek' => '00025',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '5.1.02.01.001.00025',
                'uraian' => 'Belanja Alat/Bahan untuk Kegiatan Kantor- Kertas dan Cover',
            ],
            [
                'akun' => '5',
                'kelompok' => '1',
                'jenis' => '02',
                'objek' => '01',
                'rincian_objek' => '001',
                'sub_rincian_objek' => '00026',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '5.1.02.01.001.00026',
                'uraian' => 'Belanja Alat/Bahan untuk Kegiatan Kantor-Bahan Cetak',
            ],
            [
                'akun' => '5',
                'kelompok' => '1',
                'jenis' => '02',
                'objek' => '01',
                'rincian_objek' => '001',
                'sub_rincian_objek' => '00029',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '5.1.02.01.001.00029',
                'uraian' => 'Belanja Alat/Bahan untuk Kegiatan Kantor-Bahan Komputer',
            ],
            [
                'akun' => '5',
                'kelompok' => '1',
                'jenis' => '02',
                'objek' => '01',
                'rincian_objek' => '001',
                'sub_rincian_objek' => '00030',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '5.1.02.01.001.00030',
                'uraian' => 'Belanja Alat/Bahan untuk Kegiatan Kantor-Perabot Kantor',
            ],
            [
                'akun' => '5',
                'kelompok' => '1',
                'jenis' => '02',
                'objek' => '01',
                'rincian_objek' => '001',
                'sub_rincian_objek' => '00043',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '5.1.02.01.001.00043',
                'uraian' => 'Belanja Natura dan Pakan-Natura',
            ],
            [
                'akun' => '5',
                'kelompok' => '1',
                'jenis' => '02',
                'objek' => '01',
                'rincian_objek' => '001',
                'sub_rincian_objek' => '00052',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '5.1.02.01.001.00052',
                'uraian' => 'Belanja Makanan dan Minuman Rapat',
            ],
            [
                'akun' => '5',
                'kelompok' => '1',
                'jenis' => '02',
                'objek' => '01',
                'rincian_objek' => '001',
                'sub_rincian_objek' => '00058',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '5.1.02.01.001.00058',
                'uraian' => 'Belanja Makanan dan Minuman Aktivitas Lapangan',
            ],
            [
                'akun' => '5',
                'kelompok' => '1',
                'jenis' => '02',
                'objek' => '02',
                'rincian_objek' => '001',
                'sub_rincian_objek' => '00025',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '5.1.02.02.001.00025',
                'uraian' => 'Belanja Jasa Tenaga Kesenian dan Kebudayaan',
            ],
            [
                'akun' => '5',
                'kelompok' => '1',
                'jenis' => '02',
                'objek' => '02',
                'rincian_objek' => '001',
                'sub_rincian_objek' => '00048',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '5.1.02.02.001.00048',
                'uraian' => 'Belanja Jasa Kontribusi Asosiasi',
            ],
            [
                'akun' => '5',
                'kelompok' => '1',
                'jenis' => '02',
                'objek' => '01',
                'rincian_objek' => '001',
                'sub_rincian_objek' => '00004',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '5.1.02.01.001.00004',
                'uraian' => 'Belanja Bahan-Bahan Bakar dan Pelumas',
            ],
            [
                'akun' => '5',
                'kelompok' => '1',
                'jenis' => '02',
                'objek' => '04',
                'rincian_objek' => '001',
                'sub_rincian_objek' => '00003',
                'sub_sub_rincian_objek' => '000',
                'kode_lengkap' => '5.1.02.04.001.00003',
                'uraian' => 'Belanja Perjalanan Dinas Dalam Kota',
            ],
        ];

        foreach ($kodeRekeningData as $data) {
            KodeRekening::updateOrCreate(['kode_lengkap' => $data['kode_lengkap']], $data);
        }

        $this->command->info('Kode Rekening berhasil disimpan.');
    }
}

