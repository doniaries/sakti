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

        // Hanya untuk Bidang Pemasaran Pariwisata (bidang lain dibiarkan kosong sesuai permintaan)
        $pemasaran = Bidang::where('nama', 'LIKE', '%Pemasaran%')->first();
        $bidangId = $pemasaran?->id ?? 2;

        $tahunList = [2026, 2027];

        /**
         * 3 Dokumen Resmi SIPD-RI RKA Rincian Belanja SKPD untuk Bidang Pemasaran Pariwisata:
         * 1. Cetak RKA 3.26.03.2.01.0006 - Fasilitasi Kegiatan Pemasaran Pariwisata (Pagu: Rp 47.000.200,00)
         * 2. Cetak RKA 3.26.04.2.02.0014 - Fasilitasi penguatan Kekayaan Intelektual (Pagu: Rp 3.999.900,00)
         * 3. Cetak RKA 3.26.05.2.01.0006 - Fasilitasi Pengembangan Kompetensi SDM Ekonomi Kreatif (Pagu: Rp 18.272.100,00)
         */
        $programsConfig = [
            [
                'kode_program' => '3.26.03',
                'nama_program' => 'PROGRAM PEMASARAN PARIWISATA',
                'kegiatan' => [
                    'kode_kegiatan' => '3.26.03.2.01',
                    'nama_kegiatan' => 'Pemasaran Pariwisata Dalam dan Luar Negeri Daya Tarik, Destinasi dan Kawasan Strategis Pariwisata Kabupaten/Kota',
                    'pagu_anggaran' => 47000200, // Rp 47.000.200,00
                    'sub_kegiatan' => [
                        'kode_sub_kegiatan' => '3.26.03.2.01.0006',
                        'nama_sub_kegiatan' => 'Fasilitasi Kegiatan Pemasaran Pariwisata Baik Dalam dan Luar Negeri Pariwisata Kabupaten/Kota',
                    ],
                ],
            ],
            [
                'kode_program' => '3.26.04',
                'nama_program' => 'PROGRAM PENGEMBANGAN EKONOMI KREATIF MELALUI PEMANFAATAN DAN PERLINDUNGAN HAK KEKAYAAN INTELEKTUAL',
                'kegiatan' => [
                    'kode_kegiatan' => '3.26.04.2.02',
                    'nama_kegiatan' => 'Pengembangan Ekosistem Ekonomi Kreatif',
                    'pagu_anggaran' => 3999900, // Rp 3.999.900,00
                    'sub_kegiatan' => [
                        'kode_sub_kegiatan' => '3.26.04.2.02.0014',
                        'nama_sub_kegiatan' => 'Fasilitasi penguatan Kekayaan Intelektual',
                    ],
                ],
            ],
            [
                'kode_program' => '3.26.05',
                'nama_program' => 'PROGRAM PENGEMBANGAN SUMBER DAYA PARIWISATA DAN EKONOMI KREATIF',
                'kegiatan' => [
                    'kode_kegiatan' => '3.26.05.2.01',
                    'nama_kegiatan' => 'Pelaksanaan Peningkatan Kapasitas Sumber Daya Manusia Pariwisata dan Ekonomi Kreatif Tingkat Dasar',
                    'pagu_anggaran' => 18272100, // Rp 18.272.100,00
                    'sub_kegiatan' => [
                        'kode_sub_kegiatan' => '3.26.05.2.01.0006',
                        'nama_sub_kegiatan' => 'Fasilitasi Pengembangan Kompetensi Sumber Daya Manusia Ekonomi Kreatif',
                    ],
                ],
            ],
        ];

        foreach ($tahunList as $thn) {
            foreach ($programsConfig as $prog) {
                $program = MasterProgram::create([
                    'tahun_anggaran' => $thn,
                    'kode_program' => $prog['kode_program'],
                    'nama_program' => $prog['nama_program'],
                ]);

                $kegiatan = MasterKegiatan::create([
                    'bidang_id' => $bidangId,
                    'program_id' => $program->id,
                    'tahun_anggaran' => $thn,
                    'kode_kegiatan' => $prog['kegiatan']['kode_kegiatan'],
                    'nama_kegiatan' => $prog['kegiatan']['nama_kegiatan'],
                    'pagu_anggaran' => $prog['kegiatan']['pagu_anggaran'],
                ]);

                MasterSubKegiatan::create([
                    'kegiatan_id' => $kegiatan->id,
                    'tahun_anggaran' => $thn,
                    'kode_sub_kegiatan' => $prog['kegiatan']['sub_kegiatan']['kode_sub_kegiatan'],
                    'nama_sub_kegiatan' => $prog['kegiatan']['sub_kegiatan']['nama_sub_kegiatan'],
                ]);
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
                    'sub_sub_rincian_objek' => '00',
                    'uraian' => $rek['uraian'],
                ]
            );
            $rekeningMap[$rek['kode_lengkap']] = $rekObj->id;
        }

        // Daftar Rincian Barang Riil dari 3 Lampiran RKA SIPD
        $barangRkaList = [
            // ATK & Cetakan
            [
                'kode_rekening' => '5.1.02.01.001.00024',
                'kode_barang' => '5.1.02.01.001.00024.001',
                'nama_barang' => 'Barang Cetakan - Fotocopy Biasa',
                'satuan' => 'Lembar',
                'harga_satuan' => 300,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00024',
                'kode_barang' => '5.1.02.01.001.00024.002',
                'nama_barang' => 'Barang Cetakan - Kwitansi NCR, 5 ply',
                'satuan' => 'Pack',
                'harga_satuan' => 46300,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00024',
                'kode_barang' => '5.1.02.01.001.00024.003',
                'nama_barang' => 'Map - Map Goby',
                'satuan' => 'Buah',
                'harga_satuan' => 33500,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00024',
                'kode_barang' => '5.1.02.01.001.00024.004',
                'nama_barang' => 'Alat Tulis - Pena Boldliner',
                'satuan' => 'Buah',
                'harga_satuan' => 22200,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00024',
                'kode_barang' => '5.1.02.01.001.00024.005',
                'nama_barang' => 'Alat Tulis - Pena My-Gel',
                'satuan' => 'Lusin',
                'harga_satuan' => 127000,
            ],
            // Kertas & Cover
            [
                'kode_rekening' => '5.1.02.01.001.00025',
                'kode_barang' => '5.1.02.01.001.00025.001',
                'nama_barang' => 'Kertas HVS Sidu, ukuran F4, 75 gram',
                'satuan' => 'Rim',
                'harga_satuan' => 65500,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00025',
                'kode_barang' => '5.1.02.01.001.00025.002',
                'nama_barang' => 'Kertas HVS - A4, 75 gram',
                'satuan' => 'Rim',
                'harga_satuan' => 66200,
            ],
            [
                'kode_rekening' => '5.1.02.01.001.00025',
                'kode_barang' => '5.1.02.01.001.00025.003',
                'nama_barang' => 'Kertas HVS - F4, 75 gram (Pengadaan Khusus)',
                'satuan' => 'Rim',
                'harga_satuan' => 79400,
            ],
            // Bahan Komputer & Tinta
            [
                'kode_rekening' => '5.1.02.01.001.00029',
                'kode_barang' => '5.1.02.01.001.00029.001',
                'nama_barang' => 'Tinta/Toner Printer - Tinta Printer Epson 001 Black',
                'satuan' => 'Botol',
                'harga_satuan' => 150000,
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

        // Sinkronisasi ulang pengajuan SPJ eksisting agar terhubung ke kegiatan & sub kegiatan Bidang Pemasaran
        $subPemasaran = MasterSubKegiatan::where('tahun_anggaran', 2026)->first();
        if ($subPemasaran) {
            PengajuanSpj::query()->update([
                'bidang_id' => $bidangId,
                'kegiatan_id' => $subPemasaran->kegiatan_id,
                'sub_kegiatan_id' => $subPemasaran->id,
                'tahun_anggaran' => 2026,
            ]);
        }

        $this->command->info('Seeder berhasil: Hanya Bidang Pemasaran Pariwisata yang diisi sesuai 3 file PDF SIPD-RI (Program, Kegiatan, Sub Kegiatan, Rekening, dan Barang). Bidang lainnya kosong.');
    }
}
