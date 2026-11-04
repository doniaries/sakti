<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\MasterKegiatan;
use App\Models\MasterProgram;
use App\Models\MasterSubKegiatan;
use App\Models\PengajuanSpj;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KegiatanSubKegiatanSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterSubKegiatan::truncate();
        MasterKegiatan::truncate();
        MasterProgram::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $sekretariat = Bidang::where('nama', 'LIKE', '%Sekretariat%')->first();
        $pemasaran = Bidang::where('nama', 'LIKE', '%Pemasaran%')->first();
        $destinasi = Bidang::where('nama', 'LIKE', '%Destinasi%')->first();
        $pora = Bidang::where('nama', 'LIKE', '%Pemuda%')->first();

        $tahun = 2026;

        $programsData = [
            // =========================================================================
            // 1. BIDANG PEMASARAN PARIWISATA (SESUAI LAMPIRAN SIPD RESMI)
            // =========================================================================
            [
                'bidang_id' => $pemasaran?->id ?? 2,
                'kode_program' => '3.26.04',
                'nama_program' => 'PROGRAM PENGEMBANGAN EKONOMI KREATIF MELALUI PEMANFAATAN DAN PERLINDUNGAN HAK KEKAYAAN INTELEKTUAL',
                'kegiatans' => [
                    [
                        'kode_kegiatan' => '3.26.04.2.02',
                        'nama_kegiatan' => 'Pengembangan Ekosistem Ekonomi Kreatif',
                        'pagu_anggaran' => 150000000,
                        'sub_kegiatan' => [
                            [
                                'kode_sub_kegiatan' => '3.26.04.2.02.0014',
                                'nama_sub_kegiatan' => 'Fasilitasi penguatan Kekayaan Intelektual',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'bidang_id' => $pemasaran?->id ?? 2,
                'kode_program' => '3.26.05',
                'nama_program' => 'PROGRAM PENGEMBANGAN SUMBER DAYA PARIWISATA DAN EKONOMI KREATIF',
                'kegiatans' => [
                    [
                        'kode_kegiatan' => '3.26.05.2.01',
                        'nama_kegiatan' => 'Pelaksanaan Peningkatan Kapasitas Sumber Daya Manusia Pariwisata dan Ekonomi Kreatif Tingkat Dasar',
                        'pagu_anggaran' => 125000000,
                        'sub_kegiatan' => [
                            [
                                'kode_sub_kegiatan' => '3.26.05.2.01.0006',
                                'nama_sub_kegiatan' => 'Fasilitasi Pengembangan Kompetensi Sumber Daya Manusia Ekonomi Kreatif',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'bidang_id' => $pemasaran?->id ?? 2,
                'kode_program' => '3.26.03',
                'nama_program' => 'PROGRAM PEMASARAN PARIWISATA',
                'kegiatans' => [
                    [
                        'kode_kegiatan' => '3.26.03.2.01',
                        'nama_kegiatan' => 'Pemasaran Pariwisata Nusantara dan Mancanegara',
                        'pagu_anggaran' => 135000000,
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
                ],
            ],

            // =========================================================================
            // 2. BIDANG PENGEMBANGAN DESTINASI PARIWISATA
            // =========================================================================
            [
                'bidang_id' => $destinasi?->id ?? 3,
                'kode_program' => '3.26.02',
                'nama_program' => 'PROGRAM PENINGKATAN DAYA TARIK DESTINASI PARIWISATA',
                'kegiatans' => [
                    [
                        'kode_kegiatan' => '3.26.02.2.01',
                        'nama_kegiatan' => 'Pengembangan Daya Tarik Wisata Daerah Kabupaten/Kota',
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
                ],
            ],

            // =========================================================================
            // 3. BIDANG SEKRETARIAT
            // =========================================================================
            [
                'bidang_id' => $sekretariat?->id ?? 1,
                'kode_program' => '1.01.02',
                'nama_program' => 'PROGRAM PENUNJANG URUSAN PEMERINTAHAN DAERAH KABUPATEN/KOTA',
                'kegiatans' => [
                    [
                        'kode_kegiatan' => '1.01.02.1.01',
                        'nama_kegiatan' => 'Administrasi Umum dan Penunjang Urusan Pemerintahan Daerah',
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
                ],
            ],

            // =========================================================================
            // 4. BIDANG PEMUDA DAN OLAHRAGA
            // =========================================================================
            [
                'bidang_id' => $pora?->id ?? 4,
                'kode_program' => '2.19.02',
                'nama_program' => 'PROGRAM PENGEMBANGAN KAPASITAS DAYA SAING KEPEMUDAAN',
                'kegiatans' => [
                    [
                        'kode_kegiatan' => '2.19.02.2.01',
                        'nama_kegiatan' => 'Pemberdayaan dan Pengembangan Organisasi Kepemudaan Daerah',
                        'pagu_anggaran' => 85000000,
                        'sub_kegiatan' => [
                            [
                                'kode_sub_kegiatan' => '2.19.02.2.01.0001',
                                'nama_sub_kegiatan' => 'Pemberdayaan dan Pengembangan Organisasi Kepemudaan',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'bidang_id' => $pora?->id ?? 4,
                'kode_program' => '2.19.03',
                'nama_program' => 'PROGRAM PEMBINAAN DAN PENGEMBANGAN OLAHRAGA',
                'kegiatans' => [
                    [
                        'kode_kegiatan' => '2.19.03.2.01',
                        'nama_kegiatan' => 'Pembinaan dan Pengembangan Olahraga Prestasi Daerah',
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
                ],
            ],
        ];

        foreach ($programsData as $progItem) {
            $program = MasterProgram::create([
                'tahun_anggaran' => $tahun,
                'kode_program' => $progItem['kode_program'],
                'nama_program' => $progItem['nama_program'],
            ]);

            foreach ($progItem['kegiatans'] as $kegItem) {
                $kegiatan = MasterKegiatan::create([
                    'bidang_id' => $progItem['bidang_id'],
                    'program_id' => $program->id,
                    'tahun_anggaran' => $tahun,
                    'kode_kegiatan' => $kegItem['kode_kegiatan'],
                    'nama_kegiatan' => $kegItem['nama_kegiatan'],
                    'pagu_anggaran' => $kegItem['pagu_anggaran'] ?? 0,
                ]);

                foreach ($kegItem['sub_kegiatan'] as $subItem) {
                    MasterSubKegiatan::create([
                        'kegiatan_id' => $kegiatan->id,
                        'tahun_anggaran' => $tahun,
                        'kode_sub_kegiatan' => $subItem['kode_sub_kegiatan'],
                        'nama_sub_kegiatan' => $subItem['nama_sub_kegiatan'],
                    ]);
                }
            }
        }

        // Sinkronisasi ulang pengajuan SPJ eksisting agar terhubung ke kegiatan & sub kegiatan baru sesuai bidangnya
        $spjs = PengajuanSpj::all();
        foreach ($spjs as $spj) {
            $firstSub = MasterSubKegiatan::whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $spj->bidang_id))->first()
                ?? MasterSubKegiatan::first();

            if ($firstSub) {
                $spj->kegiatan_id = $firstSub->kegiatan_id;
                $spj->sub_kegiatan_id = $firstSub->id;
                $spj->tahun_anggaran = $tahun;
                $spj->saveQuietly();
            }
        }

        $this->command->info('Seeder Master Program, Kegiatan & Sub Kegiatan (Bidang Pemasaran Pariwisata & seluruh bidang) berhasil diperbarui.');
    }
}
