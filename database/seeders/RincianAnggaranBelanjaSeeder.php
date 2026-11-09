<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\KodeRekening;
use App\Models\MasterBarang;
use App\Models\MasterSubKegiatan;
use App\Models\RincianAnggaranBelanja;
use App\Models\Satuan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RincianAnggaranBelanjaSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        RincianAnggaranBelanja::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $tahunList = [2026];

        $rekeningCache = KodeRekening::all()->keyBy('kode_lengkap');
        $barangCache = MasterBarang::all()->keyBy('nama_barang');

        $sekretariat = Bidang::where('nama', 'LIKE', '%Sekretariat%')->first();
        $pemasaran = Bidang::where('nama', 'LIKE', '%Pemasaran%')->first();
        $destinasi = Bidang::where('nama', 'LIKE', '%Destinasi%')->first();
        $pora = Bidang::where('nama', 'LIKE', '%Pemuda%')->orWhere('nama', 'LIKE', '%Olahraga%')->first();

        $bidangMap = [
            1 => $sekretariat?->id ?? 1,
            2 => $pemasaran?->id ?? 2,
            3 => $destinasi?->id ?? 3,
            4 => $pora?->id ?? 4,
        ];

        /**
         * Master Rincian Anggaran Belanja (RKA) Komprehensif untuk SELURUH 4 Bidang Dinas
         */
        $rkaDataBySubKegiatan = [
            // =========================================================================
            // BIDANG 1: SEKRETARIAT
            // =========================================================================
            // Sub Kegiatan: 3.26.01.2.06.0002 (Penyediaan Bahan Logistik Kantor)
            '3.26.01.2.06.0002' => [
                'bidang_id' => $bidangMap[1],
                'items' => [
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Kertas HVS A4 80 gram Sidu',
                        'spesifikasi' => 'Kertas HVS A4 merk Sinar Dunia 80 gr administrasi perkantoran',
                        'volume' => 30,
                        'satuan' => 'Rim',
                        'harga_satuan' => 55000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Kertas HVS F4 75 gram Sidu',
                        'spesifikasi' => 'Kertas HVS F4 Sidu 75 gr dokumen surat keputusan dinas',
                        'volume' => 35,
                        'satuan' => 'Rim',
                        'harga_satuan' => 65500,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Pena Signo Uniball 0.5',
                        'spesifikasi' => 'Pena Signo Uniball 0.5 mm warna hitam original',
                        'volume' => 60,
                        'satuan' => 'Buah',
                        'harga_satuan' => 23000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Spidol Whiteboard Snowman',
                        'spesifikasi' => 'Spidol whiteboard merk Snowman isi 12 hitam/biru',
                        'volume' => 20,
                        'satuan' => 'Pack',
                        'harga_satuan' => 75000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00029',
                        'nama_item' => 'Tinta Printer Epson 001 Black',
                        'spesifikasi' => 'Tinta printer Epson 001 Black 127ml original untuk printer L-Series',
                        'volume' => 15,
                        'satuan' => 'Botol',
                        'harga_satuan' => 150000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00029',
                        'nama_item' => 'Tinta Printer Epson 001 Color (Cyan/Magenta/Yellow)',
                        'spesifikasi' => 'Tinta printer Epson 001 warna original 70ml',
                        'volume' => 18,
                        'satuan' => 'Botol',
                        'harga_satuan' => 130000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00029',
                        'nama_item' => 'Flashdisk Sandisk 32GB USB 3.0',
                        'spesifikasi' => 'Flashdisk penyimpanan data administrasi 32GB',
                        'volume' => 12,
                        'satuan' => 'Buah',
                        'harga_satuan' => 95000,
                    ],
                ],
            ],

            // Sub Kegiatan: 3.26.01.2.06.0005 (Penyediaan Barang Cetakan dan Penggandaan)
            '3.26.01.2.06.0005' => [
                'bidang_id' => $bidangMap[1],
                'items' => [
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Fotocopy Dokumen & Laporan Dinas',
                        'spesifikasi' => 'Penggandaan berkas administrasi umum dan surat kedinasan',
                        'volume' => 6000,
                        'satuan' => 'Lembar',
                        'harga_satuan' => 300,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Map Goby Plastik Arsip',
                        'spesifikasi' => 'Map arsip tebal goby penyimpan dokumen SPJ dan kepegawaian',
                        'volume' => 50,
                        'satuan' => 'Buah',
                        'harga_satuan' => 33500,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Buku Kwitansi Keuangan NCR 5 Ply',
                        'spesifikasi' => 'Buku kwitansi bendahara pengeluaran 5 rangkap NCR',
                        'volume' => 25,
                        'satuan' => 'Pack',
                        'harga_satuan' => 46300,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Spanduk / Banner Publikasi Kedinasan',
                        'spesifikasi' => 'Cetak banner flexy outdoor informasi pelayanan dinas',
                        'volume' => 40,
                        'satuan' => 'Meter',
                        'harga_satuan' => 45000,
                    ],
                ],
            ],

            // Sub Kegiatan: 3.26.01.2.02.0005 (Koordinasi dan Penyusunan Laporan Keuangan Akhir Tahun)
            '3.26.01.2.02.0005' => [
                'bidang_id' => $bidangMap[1],
                'items' => [
                    [
                        'kode_rekening' => '5.1.02.01.001.00052',
                        'nama_item' => 'Snack Rapat Koordinasi Laporan Keuangan',
                        'spesifikasi' => 'Kue kotak isi 3 kue + air mineral rapat penyusunan neraca SKPD',
                        'volume' => 200,
                        'satuan' => 'Kotak',
                        'harga_satuan' => 15000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00052',
                        'nama_item' => 'Nasi Kotak Pembahasan LKPD Akhir Tahun',
                        'spesifikasi' => 'Makan rapat koordinasi penyusunan laporan keuangan dengan BPKAD',
                        'volume' => 120,
                        'satuan' => 'Kotak',
                        'harga_satuan' => 28000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Penjilidan Hardcover Laporan Keuangan SKPD',
                        'spesifikasi' => 'Jilid hardcover foil emas buku laporan keuangan tahunan',
                        'volume' => 15,
                        'satuan' => 'Buku',
                        'harga_satuan' => 85000,
                    ],
                ],
            ],

            // =========================================================================
            // BIDANG 2: PEMASARAN PARIWISATA
            // =========================================================================
            // Sub Kegiatan: 3.26.03.2.01.0006 (Fasilitasi Kegiatan Pemasaran Pariwisata) - DPA 2027 SIPD: Rp 47.000.200
            '3.26.03.2.01.0006' => [
                'bidang_id' => $bidangMap[2],
                'items' => [
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Barang Cetakan - Fotocopy Biasa',
                        'spesifikasi' => 'Barang Cetakan - Fotocopy Biasa kegiatan pemasaran pariwisata',
                        'volume' => 48,
                        'satuan' => 'Lembar',
                        'harga_satuan' => 300,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Kertas HVS Sidu, ukuran F4, 75 gram',
                        'spesifikasi' => 'Kertas HVS Sidu, ukuran F4, 75 gram administrasi pemasaran',
                        'volume' => 2,
                        'satuan' => 'Rim',
                        'harga_satuan' => 65500,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00052',
                        'nama_item' => 'Makanan - Kue Kotak isi 3 kue + air',
                        'spesifikasi' => 'Makanan - Kue Kotak isi 3 kue + air 20 x pertemuan',
                        'volume' => 330,
                        'satuan' => 'Kotak',
                        'harga_satuan' => 13300,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00052',
                        'nama_item' => 'Bahan Masakan/Minuman/Kue - Kopi Cap Rangkiang',
                        'spesifikasi' => 'Bahan Masakan/Minuman/Kue - Kopi Cap Rangkiang Khas',
                        'volume' => 4,
                        'satuan' => 'Kg',
                        'harga_satuan' => 157750,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00052',
                        'nama_item' => 'Bahan Masakan/Minuman/Kue - Gula Pasir',
                        'spesifikasi' => 'Bahan Masakan/Minuman/Kue - Gula Pasir premium',
                        'volume' => 2,
                        'satuan' => 'Kg',
                        'harga_satuan' => 21200,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00058',
                        'nama_item' => 'Makanan - Nasi Goreng Lapangan',
                        'spesifikasi' => 'Makanan - Nasi Goreng untuk konsumsi aktivitas lapangan',
                        'volume' => 1482,
                        'satuan' => 'Bungkus',
                        'harga_satuan' => 20200,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00058',
                        'nama_item' => 'Makanan - Nasi Bungkus 1 Protein',
                        'spesifikasi' => 'Makanan - Nasi Bungkus 1 Protein konsumsi lapangan',
                        'volume' => 74,
                        'satuan' => 'Bungkus',
                        'harga_satuan' => 24000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.02.001.00025',
                        'nama_item' => 'Beban Jasa Tenaga Kesenian dan Kebudayaan - Upah Pekerja Seni',
                        'spesifikasi' => 'Upah Pekerja Seni kegiatan even promosi pariwisata',
                        'volume' => 84,
                        'satuan' => 'Orang',
                        'harga_satuan' => 120000,
                    ],
                ],
            ],

            // Sub Kegiatan: 3.26.04.2.02.0014 (Fasilitasi Penguatan Kekayaan Intelektual)
            '3.26.04.2.02.0014' => [
                'bidang_id' => $bidangMap[2],
                'items' => [
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Kertas HVS - A4, 75 gram',
                        'spesifikasi' => 'Kertas HVS A4 formulir sertifikat HKI pelaku ekraf',
                        'volume' => 10,
                        'satuan' => 'Rim',
                        'harga_satuan' => 66200,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Barang Cetakan - Fotocopy Berkas HKI',
                        'spesifikasi' => 'Fotocopy berkas pendaftaran merek dan hak cipta',
                        'volume' => 800,
                        'satuan' => 'Lembar',
                        'harga_satuan' => 300,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00043',
                        'nama_item' => 'Snack Kotak Pendampingan Pendaftaran Merek',
                        'spesifikasi' => 'Snack kotak pendampingan HKI pelaku usaha ekonomi kreatif',
                        'volume' => 100,
                        'satuan' => 'Kotak',
                        'harga_satuan' => 15000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.02.001.00048',
                        'nama_item' => 'Beban Jasa Kontribusi Asosiasi - Bimtek HKI',
                        'spesifikasi' => 'Kontribusi kegiatan bimtek/kursus HKI Kemenkumham',
                        'volume' => 2,
                        'satuan' => 'Paket',
                        'harga_satuan' => 2500000,
                    ],
                ],
            ],

            // =========================================================================
            // BIDANG 3: PENGEMBANGAN DESTINASI PARIWISATA
            // =========================================================================
            // Sub Kegiatan: 3.26.02.2.01.0001 (Peningkatan Mutu Daya Tarik Destinasi Pariwisata)
            '3.26.02.2.01.0001' => [
                'bidang_id' => $bidangMap[3],
                'items' => [
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Lampu Sorot LED Outdoor Spot Destinasi 50W',
                        'spesifikasi' => 'Lampu LED sorot outdoor waterproof IP66 penerangan tebing Geopark',
                        'volume' => 20,
                        'satuan' => 'Unit',
                        'harga_satuan' => 275000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Kabel Listrik NYY 2x2.5mm Outdoor',
                        'spesifikasi' => 'Kabel instalasi bawah tanah tahan cuaca panjang 100 meter',
                        'volume' => 6,
                        'satuan' => 'Roll',
                        'harga_satuan' => 650000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Cat Kayu & Besi Anti Karat Signboard Wisata',
                        'spesifikasi' => 'Cat pelapis cuaca eksterior papan informasi daya tarik wisata',
                        'volume' => 25,
                        'satuan' => 'Kaleng',
                        'harga_satuan' => 95000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Papan Petunjuk Arah Kawasan Geopark Silokek',
                        'spesifikasi' => 'Plang marka kayu ukir khas Minang penunjuk spot goa dan air terjun',
                        'volume' => 12,
                        'satuan' => 'Buah',
                        'harga_satuan' => 450000,
                    ],
                ],
            ],

            // Sub Kegiatan: 3.26.02.2.01.0002 (Pemberdayaan Masyarakat dalam Pengelolaan Destinasi)
            '3.26.02.2.01.0002' => [
                'bidang_id' => $bidangMap[3],
                'items' => [
                    [
                        'kode_rekening' => '5.1.02.01.001.00052',
                        'nama_item' => 'Snack Box Pelatihan Pokdarwis Destinasi',
                        'spesifikasi' => 'Snack kue tradisional + air mineral pelatihan kelompok sadar wisata',
                        'volume' => 250,
                        'satuan' => 'Kotak',
                        'harga_satuan' => 13500,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00052',
                        'nama_item' => 'Nasi Kotak Workshop Pemandu Wisata Lokal',
                        'spesifikasi' => 'Makan siang peserta pembinaan pemandu susur gua dan arung jeram',
                        'volume' => 180,
                        'satuan' => 'Kotak',
                        'harga_satuan' => 25000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Modul Panduan Hospitality & Tata Kelola Pokdarwis',
                        'spesifikasi' => 'Buku pedoman standarisasi pelayanan homestay dan pemandu',
                        'volume' => 120,
                        'satuan' => 'Buku',
                        'harga_satuan' => 45000,
                    ],
                ],
            ],

            // =========================================================================
            // BIDANG 4: PEMUDA DAN OLAHRAGA
            // =========================================================================
            // Sub Kegiatan: 3.26.06.2.01.0001 (Penyelenggaraan Kejuaraan dan Festival Olahraga)
            '3.26.06.2.01.0001' => [
                'bidang_id' => $bidangMap[4],
                'items' => [
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Medali Kejuaraan Olahraga Daerah Set Emas/Perak/Perunggu',
                        'spesifikasi' => 'Medali logam cor grafir logo dinas tali rajut tebal',
                        'volume' => 45,
                        'satuan' => 'Set',
                        'harga_satuan' => 175000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Bola Sepak Standar Pertandingan FIFA Match',
                        'spesifikasi' => 'Bola sepak jahit manual bahan PU soft touch standar turnamen resmi',
                        'volume' => 15,
                        'satuan' => 'Buah',
                        'harga_satuan' => 385000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Bola Voli Standar Turnamen Mikasa V200W',
                        'spesifikasi' => 'Bola voli mikasa original kulit empuk standar kejuaraan daerah',
                        'volume' => 15,
                        'satuan' => 'Buah',
                        'harga_satuan' => 420000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Piagam Penghargaan Atlet & Pelatih Berprestasi',
                        'spesifikasi' => 'Piagam kertas concorde tebal cetak separasi emas',
                        'volume' => 200,
                        'satuan' => 'Lembar',
                        'harga_satuan' => 12000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Rompi Tim Latihan Atlet Porprov',
                        'spesifikasi' => 'Rompi mesh jaring bernomor dada dan punggung 12 set',
                        'volume' => 10,
                        'satuan' => 'Lusin',
                        'harga_satuan' => 240000,
                    ],
                ],
            ],

            // Sub Kegiatan: 3.26.06.2.01.0002 (Pembinaan Atlet Berbakat dan Pelatih Berprestasi)
            '3.26.06.2.01.0002' => [
                'bidang_id' => $bidangMap[4],
                'items' => [
                    [
                        'kode_rekening' => '5.1.02.01.001.00043',
                        'nama_item' => 'Susu & Nutrisi Protein Atlet Pelatda',
                        'spesifikasi' => 'Susu suplemen pemulihan stamina atlet 900gr',
                        'volume' => 50,
                        'satuan' => 'Kaleng',
                        'harga_satuan' => 145000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00052',
                        'nama_item' => 'Snack Box Pemusatan Latihan Atlet',
                        'spesifikasi' => 'Snack bervitamin + buah pisang + air mineral pemusatan latihan',
                        'volume' => 300,
                        'satuan' => 'Kotak',
                        'harga_satuan' => 15000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00052',
                        'nama_item' => 'Nasi Kotak Pembinaan Fisik Atlet Cabor',
                        'spesifikasi' => 'Makan berkalori tinggi 2 protein atlet pemusatan latihan cabor',
                        'volume' => 250,
                        'satuan' => 'Kotak',
                        'harga_satuan' => 28000,
                    ],
                    [
                        'kode_rekening' => '5.1.02.01.001.00024',
                        'nama_item' => 'Kaos Kontingen Atlet Kabupaten Sijunjung',
                        'spesifikasi' => 'Baju olahraga dry-fit sablon sublim lambang daerah',
                        'volume' => 80,
                        'satuan' => 'Pcs',
                        'harga_satuan' => 125000,
                    ],
                ],
            ],
        ];

        $totalRkaCreated = 0;

        foreach ($tahunList as $thn) {
            foreach ($rkaDataBySubKegiatan as $kodeSub => $config) {
                $subKegiatan = MasterSubKegiatan::where('tahun_anggaran', $thn)
                    ->where('kode_sub_kegiatan', $kodeSub)
                    ->first();

                if (! $subKegiatan) {
                    continue;
                }

                $bidangId = $config['bidang_id'];
                $items = $config['items'];

                foreach ($items as $item) {
                    $rek = $rekeningCache->get($item['kode_rekening']);

                    // 1. Dapatkan atau Buat Master Barang agar relasi master_barang_id 100% TIDAK KOSONG
                    $barang = $barangCache->get($item['nama_item']);
                    if (! $barang) {
                        $satuanObj = Satuan::firstOrCreate(['nama' => $item['satuan']]);
                        $cleanCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $item['nama_item']), 0, 5));
                        $barang = MasterBarang::firstOrCreate(
                            ['nama_barang' => $item['nama_item']],
                            [
                                'kode_rekening_id' => $rek?->id ?? 1,
                                'kode_barang' => "BRG-{$cleanCode}-" . rand(100, 999),
                                'uraian' => $item['spesifikasi'] ?? $item['nama_item'],
                                'satuan_id' => $satuanObj->id,
                                'harga_satuan' => $item['harga_satuan'],
                                'keterangan' => $item['spesifikasi'] ?? null,
                            ]
                        );
                        $barangCache->put($item['nama_item'], $barang);
                    }

                    // 2. Buat Rekaman Rincian Anggaran Belanja (RKA)
                    RincianAnggaranBelanja::create([
                        'tahun_anggaran' => $thn,
                        'bidang_id' => $bidangId,
                        'sub_kegiatan_id' => $subKegiatan->id,
                        'kode_rekening_id' => $rek?->id ?? 1,
                        'master_barang_id' => $barang->id,
                        'nama_item' => $item['nama_item'],
                        'spesifikasi' => $item['spesifikasi'],
                        'sumber_dana' => 'Dana Alokasi Umum (DAU)',
                        'volume' => $item['volume'],
                        'satuan' => $item['satuan'],
                        'harga_satuan' => $item['harga_satuan'],
                        'pagu_total' => $item['volume'] * $item['harga_satuan'],
                        'volume_realisasi' => 0,
                        'nilai_realisasi' => 0,
                    ]);

                    $totalRkaCreated++;
                }
            }
        }

        $this->command->info("Seeder Rincian Anggaran Belanja (RKA) berhasil: {$totalRkaCreated} item belanja lengkap untuk 4 Bidang telah dibuat dengan master_barang_id 100% valid.");
    }
}
