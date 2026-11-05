<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\KodeRekening;
use App\Models\MasterBarang;
use App\Models\MasterKegiatan;
use App\Models\MasterProgram;
use App\Models\MasterSubKegiatan;
use App\Models\PengajuanSpj;
use App\Models\Satuan;
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

        $tahunList = [2026, 2027];

        // Cari bidang-bidang dinas
        $sekretariat = Bidang::where('nama', 'LIKE', '%Sekretariat%')->first();
        $pemasaran = Bidang::where('nama', 'LIKE', '%Pemasaran%')->first();
        $destinasi = Bidang::where('nama', 'LIKE', '%Destinasi%')->first();
        $pora = Bidang::where('nama', 'LIKE', '%Pemuda%')->orWhere('nama', 'LIKE', '%Olahraga%')->first();

        $sekretariatId = $sekretariat?->id ?? 1;
        $pemasaranId = $pemasaran?->id ?? 2;
        $destinasiId = $destinasi?->id ?? 3;
        $poraId = $pora?->id ?? 4;

        /**
         * Konfigurasi Program, Kegiatan, dan Sub-Kegiatan untuk SELURUH Bidang Dinas
         */
        $allProgramsConfig = [
            // 1. BIDANG SEKRETARIAT
            [
                'bidang_id' => $sekretariatId,
                'kode_program' => '3.26.01',
                'nama_program' => 'PROGRAM PENUNJANG URUSAN PEMERINTAHAN DAERAH KABUPATEN/KOTA',
                'kegiatans' => [
                    [
                        'kode_kegiatan' => '3.26.01.2.02',
                        'nama_kegiatan' => 'Administrasi Keuangan Perangkat Daerah',
                        'pagu_anggaran' => 150000000,
                        'sub_kegiatans' => [
                            [
                                'kode_sub_kegiatan' => '3.26.01.2.02.0001',
                                'nama_sub_kegiatan' => 'Penyediaan Gaji dan Tunjangan ASN',
                            ],
                            [
                                'kode_sub_kegiatan' => '3.26.01.2.02.0005',
                                'nama_sub_kegiatan' => 'Koordinasi dan Penyusunan Laporan Keuangan Akhir Tahun SKPD',
                            ],
                        ],
                    ],
                    [
                        'kode_kegiatan' => '3.26.01.2.06',
                        'nama_kegiatan' => 'Administrasi Umum Perangkat Daerah',
                        'pagu_anggaran' => 85000000,
                        'sub_kegiatans' => [
                            [
                                'kode_sub_kegiatan' => '3.26.01.2.06.0001',
                                'nama_sub_kegiatan' => 'Penyediaan Komponen Instalasi Listrik/Penerangan Bangunan Kantor',
                            ],
                            [
                                'kode_sub_kegiatan' => '3.26.01.2.06.0002',
                                'nama_sub_kegiatan' => 'Penyediaan Bahan Logistik Kantor',
                            ],
                            [
                                'kode_sub_kegiatan' => '3.26.01.2.06.0005',
                                'nama_sub_kegiatan' => 'Penyediaan Barang Cetakan dan Penggandaan',
                            ],
                        ],
                    ],
                ],
            ],

            // 2. BIDANG PEMASARAN PARIWISATA
            [
                'bidang_id' => $pemasaranId,
                'kode_program' => '3.26.03',
                'nama_program' => 'PROGRAM PEMASARAN PARIWISATA',
                'kegiatans' => [
                    [
                        'kode_kegiatan' => '3.26.03.2.01',
                        'nama_kegiatan' => 'Pemasaran Pariwisata Dalam dan Luar Negeri Daya Tarik, Destinasi dan Kawasan Strategis Pariwisata Kabupaten/Kota',
                        'pagu_anggaran' => 47000200,
                        'sub_kegiatans' => [
                            [
                                'kode_sub_kegiatan' => '3.26.03.2.01.0006',
                                'nama_sub_kegiatan' => 'Fasilitasi Kegiatan Pemasaran Pariwisata Baik Dalam dan Luar Negeri Pariwisata Kabupaten/Kota',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'bidang_id' => $pemasaranId,
                'kode_program' => '3.26.04',
                'nama_program' => 'PROGRAM PENGEMBANGAN EKONOMI KREATIF MELALUI PEMANFAATAN DAN PERLINDUNGAN HAK KEKAYAAN INTELEKTUAL',
                'kegiatans' => [
                    [
                        'kode_kegiatan' => '3.26.04.2.02',
                        'nama_kegiatan' => 'Pengembangan Ekosistem Ekonomi Kreatif',
                        'pagu_anggaran' => 3999900,
                        'sub_kegiatans' => [
                            [
                                'kode_sub_kegiatan' => '3.26.04.2.02.0014',
                                'nama_sub_kegiatan' => 'Fasilitasi penguatan Kekayaan Intelektual',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'bidang_id' => $pemasaranId,
                'kode_program' => '3.26.05',
                'nama_program' => 'PROGRAM PENGEMBANGAN SUMBER DAYA PARIWISATA DAN EKONOMI KREATIF',
                'kegiatans' => [
                    [
                        'kode_kegiatan' => '3.26.05.2.01',
                        'nama_kegiatan' => 'Pelaksanaan Peningkatan Kapasitas Sumber Daya Manusia Pariwisata dan Ekonomi Kreatif Tingkat Dasar',
                        'pagu_anggaran' => 18272100,
                        'sub_kegiatans' => [
                            [
                                'kode_sub_kegiatan' => '3.26.05.2.01.0006',
                                'nama_sub_kegiatan' => 'Fasilitasi Pengembangan Kompetensi Sumber Daya Manusia Ekonomi Kreatif',
                            ],
                        ],
                    ],
                ],
            ],

            // 3. BIDANG PENGEMBANGAN DESTINASI PARIWISATA
            [
                'bidang_id' => $destinasiId,
                'kode_program' => '3.26.02',
                'nama_program' => 'PROGRAM PENGEMBANGAN DESTINASI PARIWISATA',
                'kegiatans' => [
                    [
                        'kode_kegiatan' => '3.26.02.2.01',
                        'nama_kegiatan' => 'Pengelolaan Destinasi Pariwisata Kabupaten/Kota',
                        'pagu_anggaran' => 95000000,
                        'sub_kegiatans' => [
                            [
                                'kode_sub_kegiatan' => '3.26.02.2.01.0001',
                                'nama_sub_kegiatan' => 'Peningkatan Mutu Daya Tarik Destinasi Pariwisata Daerah',
                            ],
                            [
                                'kode_sub_kegiatan' => '3.26.02.2.01.0002',
                                'nama_sub_kegiatan' => 'Pemberdayaan Masyarakat dalam Pengelolaan Destinasi Pariwisata',
                            ],
                        ],
                    ],
                ],
            ],

            // 4. BIDANG PEMUDA DAN OLAHRAGA
            [
                'bidang_id' => $poraId,
                'kode_program' => '3.26.06',
                'nama_program' => 'PROGRAM PENGEMBANGAN KAPASITAS DAYA SAING KEPEMUDAAN DAN KEOLAHRAGAAN',
                'kegiatans' => [
                    [
                        'kode_kegiatan' => '3.26.06.2.01',
                        'nama_kegiatan' => 'Pembinaan dan Pengembangan Olahraga Pendidikan, Rekreasi, dan Prestasi',
                        'pagu_anggaran' => 120000000,
                        'sub_kegiatans' => [
                            [
                                'kode_sub_kegiatan' => '3.26.06.2.01.0001',
                                'nama_sub_kegiatan' => 'Penyelenggaraan Kejuaraan dan Festival Olahraga Tingkat Kabupaten/Kota',
                            ],
                            [
                                'kode_sub_kegiatan' => '3.26.06.2.01.0002',
                                'nama_sub_kegiatan' => 'Pembinaan Atlet Berbakat dan Pelatih Olahraga Berprestasi',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($tahunList as $thn) {
            foreach ($allProgramsConfig as $prog) {
                $program = MasterProgram::create([
                    'tahun_anggaran' => $thn,
                    'kode_program' => $prog['kode_program'],
                    'nama_program' => $prog['nama_program'],
                ]);

                foreach ($prog['kegiatans'] as $kegData) {
                    $kegiatan = MasterKegiatan::create([
                        'bidang_id' => $prog['bidang_id'],
                        'program_id' => $program->id,
                        'tahun_anggaran' => $thn,
                        'kode_kegiatan' => $kegData['kode_kegiatan'],
                        'nama_kegiatan' => $kegData['nama_kegiatan'],
                        'pagu_anggaran' => $kegData['pagu_anggaran'],
                    ]);

                    foreach ($kegData['sub_kegiatans'] as $subData) {
                        MasterSubKegiatan::create([
                            'kegiatan_id' => $kegiatan->id,
                            'tahun_anggaran' => $thn,
                            'kode_sub_kegiatan' => $subData['kode_sub_kegiatan'],
                            'nama_sub_kegiatan' => $subData['nama_sub_kegiatan'],
                        ]);
                    }
                }
            }
        }

        // =========================================================================
        // SEED KODE REKENING BELANJA & MASTER ITEM BARANG DARI 3 LAMPIRAN SIPD RESMI
        // =========================================================================
        $satuans = [
            'Lembar', 'Rim', 'Kotak', 'Bungkus', 'Kg', 'Paket', 'Orang', 'Botol', 'Buah', 'Pack', 'Lusin'
        ];
        $satuanMap = [];
        foreach ($satuans as $s) {
            $satuanObj = Satuan::firstOrCreate(['nama' => $s]);
            $satuanMap[$s] = $satuanObj->id;
        }

        $rekeningData = [
            [
                'kode_lengkap' => '5.1.02.01.001.00024',
                'uraian' => 'Belanja Alat/Bahan untuk Kegiatan Kantor-Alat Tulis Kantor',
            ],
            [
                'kode_lengkap' => '5.1.02.01.001.00025',
                'uraian' => 'Belanja Alat/Bahan untuk Kegiatan Kantor- Kertas dan Cover',
            ],
            [
                'kode_lengkap' => '5.1.02.01.001.00029',
                'uraian' => 'Belanja Alat/Bahan untuk Kegiatan Kantor-Bahan Komputer',
            ],
            [
                'kode_lengkap' => '5.1.02.01.001.00043',
                'uraian' => 'Belanja Natura dan Pakan-Natura',
            ],
            [
                'kode_lengkap' => '5.1.02.01.001.00052',
                'uraian' => 'Belanja Makanan dan Minuman Rapat',
            ],
            [
                'kode_lengkap' => '5.1.02.01.001.00058',
                'uraian' => 'Belanja Makanan dan Minuman Aktivitas Lapangan',
            ],
            [
                'kode_lengkap' => '5.1.02.02.001.00025',
                'uraian' => 'Belanja Jasa Tenaga Kesenian dan Kebudayaan',
            ],
            [
                'kode_lengkap' => '5.1.02.02.001.00048',
                'uraian' => 'Belanja Jasa Kontribusi Asosiasi',
            ],
        ];

        $rekeningMap = [];
        foreach ($rekeningData as $rek) {
            $parts = explode('.', $rek['kode_lengkap']);
            $rekObj = KodeRekening::firstOrCreate(
                ['kode_lengkap' => $rek['kode_lengkap']],
                [
                    'akun' => $parts[0] ?? '5',
                    'kelompok' => $parts[1] ?? '1',
                    'jenis' => $parts[2] ?? '02',
                    'objek' => $parts[3] ?? '01',
                    'rincian_objek' => $parts[4] ?? '001',
                    'sub_rincian_objek' => $parts[5] ?? '00024',
                    'uraian' => $rek['uraian'],
                    'tahun_anggaran' => 2026,
                    'aktif' => true,
                ]
            );
            $rekeningMap[$rek['kode_lengkap']] = $rekObj->id;
        }

        $barangRkaList = [
            [
                'kode_rekening' => '5.1.02.01.001.00024',
                'kode_barang' => '5.1.02.01.001.00024.001',
                'nama_barang' => 'Buku Folio Isi 100 Lembar',
                'satuan' => 'Buah',
                'harga_satuan' => 24800,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00024',
                'kode_barang' => '5.1.02.01.001.00024.002',
                'nama_barang' => 'Buku Kwitansi Sedang KT 40',
                'satuan' => 'Buah',
                'harga_satuan' => 6100,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00024',
                'kode_barang' => '5.1.02.01.001.00024.003',
                'nama_barang' => 'Lem Kertas Cair 50 ml',
                'satuan' => 'Botol',
                'harga_satuan' => 4500,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00024',
                'kode_barang' => '5.1.02.01.001.00024.004',
                'nama_barang' => 'Pena Faster C600',
                'satuan' => 'Kotak',
                'harga_satuan' => 47400,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00024',
                'kode_barang' => '5.1.02.01.001.00024.005',
                'nama_barang' => 'Penghapus Whiteboard',
                'satuan' => 'Buah',
                'harga_satuan' => 11000,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00024',
                'kode_barang' => '5.1.02.01.001.00024.006',
                'nama_barang' => 'Spidol Boardmarker',
                'satuan' => 'Kotak',
                'harga_satuan' => 109200,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00025',
                'kode_barang' => '5.1.02.01.001.00025.001',
                'nama_barang' => 'Kertas F4 70 Gram Sinar Dunia',
                'satuan' => 'Rim',
                'harga_satuan' => 60700,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00025',
                'kode_barang' => '5.1.02.01.001.00025.002',
                'nama_barang' => 'Kertas A4 70 Gram Sinar Dunia',
                'satuan' => 'Rim',
                'harga_satuan' => 54100,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00029',
                'kode_barang' => '5.1.02.01.001.00029.001',
                'nama_barang' => 'Tinta/Toner Printer - Tinta Printer Epson 003 Black',
                'satuan' => 'Botol',
                'harga_satuan' => 97900,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00029',
                'kode_barang' => '5.1.02.01.001.00029.002',
                'nama_barang' => 'Tinta/Toner Printer - Tinta Printer Epson 001 Color (Cyan/Magenta/Yellow)',
                'satuan' => 'Botol',
                'harga_satuan' => 130000,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00029',
                'kode_barang' => '5.1.02.01.001.00029.003',
                'nama_barang' => 'Tinta/Toner Printer - Cartridge Printer Brother LC',
                'satuan' => 'Buah',
                'harga_satuan' => 160950,
            ],
        ];

        foreach ($barangRkaList as $item) {
            MasterBarang::updateOrCreate(
                ['kode_barang' => $item['kode_barang']],
                [
                    'kode_rekening_id' => $rekeningMap[$item['kode_rekening']] ?? 1,
                    'nama_barang' => $item['nama_barang'],
                    'satuan_id' => $satuanMap[$item['satuan']] ?? 1,
                    'harga_satuan' => $item['harga_satuan'],
                ]
            );
        }

        // Sinkronisasi ulang pengajuan SPJ eksisting agar terhubung ke kegiatan & sub kegiatan pertama yang valid
        $subFirst = MasterSubKegiatan::where('tahun_anggaran', 2026)->first();
        if ($subFirst) {
            PengajuanSpj::query()->update([
                'kegiatan_id' => $subFirst->kegiatan_id,
                'sub_kegiatan_id' => $subFirst->id,
                'tahun_anggaran' => 2026,
            ]);
        }

        $this->command->info('Seeder berhasil: Program, Kegiatan, dan Sub Kegiatan untuk SELURUH Bidang Dinas (Sekretariat, Pemasaran Pariwisata, Destinasi Pariwisata, dan Pemuda Olahraga) berhasil di-seed.');
    }
}
