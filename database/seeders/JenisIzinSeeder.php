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
                'nama' => 'Sakit (Tanpa Surat Dokter)',
                'kode' => 'sakit_tanpa_surat',
                'kategori' => 'sakit',
                'kuota_hari' => null,
                'butuh_lampiran' => false,
                'aktif' => true,
                'keterangan' => 'Izin sakit tanpa melampirkan surat keterangan dokter.',
            ],
            [
                'nama' => 'Cuti Tahunan',
                'kode' => 'cuti_tahunan',
                'kategori' => 'cuti',
                'kuota_hari' => 12,
                'butuh_lampiran' => false,
                'aktif' => true,
                'keterangan' => '12 hari kerja/tahun (PNS & PPPK minimal masa kerja 1 tahun terus-menerus, di luar cuti bersama).',
            ],
            [
                'nama' => 'Cuti Besar',
                'kode' => 'cuti_besar',
                'kategori' => 'cuti',
                'kuota_hari' => 90,
                'butuh_lampiran' => true,
                'aktif' => true,
                'keterangan' => 'Paling lama 3 bulan khusus PNS dengan masa kerja minimal 5 tahun terus-menerus.',
            ],
            [
                'nama' => 'Cuti Melahirkan',
                'kode' => 'cuti_melahirkan',
                'kategori' => 'cuti',
                'kuota_hari' => 90,
                'butuh_lampiran' => true,
                'aktif' => true,
                'keterangan' => 'Paling lama 3 bulan (PNS & PPPK) untuk persalinan anak pertama sampai dengan anak ketiga.',
            ],
            [
                'nama' => 'Cuti Karena Alasan Penting',
                'kode' => 'cuti_alasan_penting',
                'kategori' => 'cuti',
                'kuota_hari' => 30,
                'butuh_lampiran' => true,
                'aktif' => true,
                'keterangan' => 'Paling lama 1 bulan (PNS); bagi PPPK kondisi darurat keluarga memotong Cuti Tahunan (maks. 6 hari).',
            ],
            [
                'nama' => 'Cuti di Luar Tanggungan Negara (CLTN)',
                'kode' => 'cuti_cltn',
                'kategori' => 'cuti',
                'kuota_hari' => 1095,
                'butuh_lampiran' => true,
                'aktif' => true,
                'keterangan' => 'Paling lama 3 tahun (dapat diperpanjang 1 tahun) khusus PNS masa kerja min. 5 tahun, tanpa penghasilan negara.',
            ],
        ];

        // Hapus tipe WFH jika sebelumnya pernah di-seed (WFH ditetapkan admin via status_kerja user)
        JenisIzin::where('kode', 'wfh')
            ->whereDoesntHave('pengajuanIzins')
            ->delete();

        foreach ($types as $type) {
            JenisIzin::updateOrCreate(
                ['kode' => $type['kode']],
                $type
            );
        }
    }
}
