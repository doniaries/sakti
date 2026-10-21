<?php

namespace Database\Seeders;

use App\Models\JenisIzin;
use Illuminate\Database\Seeder;

class JenisIzinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'nama' => 'Izin Pribadi',
                'kode' => 'izin',
                'kategori' => 'izin',
                'kuota_hari' => null,
                'butuh_lampiran' => false,
                'aktif' => true,
                'keterangan' => 'Izin tidak hadir karena keperluan pribadi atau keluarga.',
            ],
            [
                'nama' => 'Sakit (Surat Dokter)',
                'kode' => 'sakit',
                'kategori' => 'sakit',
                'kuota_hari' => null,
                'butuh_lampiran' => true,
                'aktif' => true,
                'keterangan' => 'Izin tidak hadir karena sakit dengan melampirkan surat keterangan dokter.',
            ],
            [
                'nama' => 'Cuti Tahunan',
                'kode' => 'cuti_tahunan',
                'kategori' => 'cuti',
                'kuota_hari' => 12,
                'butuh_lampiran' => false,
                'aktif' => true,
                'keterangan' => 'Hak cuti tahunan pegawai (kuota 12 hari kerja per tahun).',
            ],
            [
                'nama' => 'Cuti Melahirkan',
                'kode' => 'cuti_melahirkan',
                'kategori' => 'cuti',
                'kuota_hari' => 90,
                'butuh_lampiran' => true,
                'aktif' => true,
                'keterangan' => 'Hak cuti bersalin bagi pegawai wanita selama 3 bulan.',
            ],
            [
                'nama' => 'Cuti Alasan Penting',
                'kode' => 'cuti_alasan_penting',
                'kategori' => 'cuti',
                'kuota_hari' => 30,
                'butuh_lampiran' => true,
                'aktif' => true,
                'keterangan' => 'Cuti karena peristiwa penting keluarga (pernikahan, kedukaan, dll).',
            ],
            [
                'nama' => 'Work From Home (WFH)',
                'kode' => 'wfh',
                'kategori' => 'wfh',
                'kuota_hari' => null,
                'butuh_lampiran' => false,
                'aktif' => true,
                'keterangan' => 'Penugasan atau izin bekerja dari rumah (WFH) yang tetap diakui sebagai kehadiran.',
            ],
        ];

        foreach ($types as $type) {
            JenisIzin::updateOrCreate(
                ['kode' => $type['kode']],
                $type
            );
        }
    }
}
