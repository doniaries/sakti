<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\KodeRekening;
use App\Models\MasterBarang;
use App\Models\MasterSubKegiatan;
use App\Models\RincianAnggaranBelanja;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RincianAnggaranBelanjaSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        RincianAnggaranBelanja::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $pemasaran = Bidang::where('nama', 'LIKE', '%Pemasaran%')->first();
        $bidangId = $pemasaran?->id ?? 2;

        $tahunList = [2026, 2027];

        $rekeningCache = KodeRekening::all()->keyBy('kode_lengkap');
        $barangCache = MasterBarang::all()->keyBy('nama_barang');

        /**
         * 3 Dokumen Resmi SIPD-RI RKA Rincian Belanja SKPD untuk Bidang Pemasaran Pariwisata
         */
        $rkaDataBySubKegiatan = [
            // =========================================================================
            // SUB KEGIATAN 1: 3.26.03.2.01.0006 (Total Pagu: Rp 47.000.200,00)
            // =========================================================================
            '3.26.03.2.01.0006' => [
                [
                    'kode_rekening' => '5.1.02.01.001.00024',
                    'nama_item' => 'Barang Cetakan - Fotocopy Biasa',
                    'spesifikasi' => 'Fotocopy bahan rapat dan materi promosi',
                    'volume' => 48,
                    'satuan' => 'Lembar',
                    'harga_satuan' => 300,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00024',
                    'nama_item' => 'Kertas HVS Sidu, ukuran F4, 75 gram',
                    'spesifikasi' => 'Kertas HVS Sidu F4 75 gr',
                    'volume' => 2,
                    'satuan' => 'Rim',
                    'harga_satuan' => 65500,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00052',
                    'nama_item' => 'Makanan/Sembako - Kue Kotak isi 3 kue + air',
                    'spesifikasi' => 'Kue kotak isi 3 kue + air mineral untuk rapat',
                    'volume' => 330,
                    'satuan' => 'Kotak',
                    'harga_satuan' => 13300,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00052',
                    'nama_item' => 'Bahan Masakan/Minuman - Kopi Cap Rangkiang',
                    'spesifikasi' => 'Kopi Cap Rangkiang Kau khas daerah',
                    'volume' => 4,
                    'satuan' => 'Kg',
                    'harga_satuan' => 157750,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00052',
                    'nama_item' => 'Bahan Masakan/Minuman - Gula Pasir',
                    'spesifikasi' => 'Gula Pasir putih premium',
                    'volume' => 2,
                    'satuan' => 'Kg',
                    'harga_satuan' => 21200,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00058',
                    'nama_item' => 'Makanan - Nasi Goreng Lapangan',
                    'spesifikasi' => 'Nasi Goreng aktivitas lapangan promosi wisata',
                    'volume' => 1482,
                    'satuan' => 'Bungkus',
                    'harga_satuan' => 20200,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00058',
                    'nama_item' => 'Makanan - Nasi Bungkus 1 Protein',
                    'spesifikasi' => 'Nasi Bungkus 1 Protein petugas event',
                    'volume' => 74,
                    'satuan' => 'Bungkus',
                    'harga_satuan' => 24000,
                ],
                [
                    'kode_rekening' => '5.1.02.02.001.00025',
                    'nama_item' => 'Beban Jasa Tenaga Kesenian - Upah Pekerja Seni',
                    'spesifikasi' => 'Honor/upah penampil seni promosi pariwisata',
                    'volume' => 84,
                    'satuan' => 'Orang',
                    'harga_satuan' => 120000,
                ],
            ],

            // =========================================================================
            // SUB KEGIATAN 2: 3.26.04.2.02.0014 (Total Pagu: Rp 3.999.900,00)
            // =========================================================================
            '3.26.04.2.02.0014' => [
                [
                    'kode_rekening' => '5.1.02.01.001.00024',
                    'nama_item' => 'Kertas HVS Sidu, ukuran F4, 75 gram',
                    'spesifikasi' => 'Kertas F4 administrasi HKI',
                    'volume' => 4,
                    'satuan' => 'Rim',
                    'harga_satuan' => 65500,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00024',
                    'nama_item' => 'Barang Cetakan - Fotocopy Biasa',
                    'spesifikasi' => 'Fotocopy berkas pendaftaran HKI',
                    'volume' => 289,
                    'satuan' => 'Lembar',
                    'harga_satuan' => 300,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00024',
                    'nama_item' => 'Kertas HVS - A4, 75 gram',
                    'spesifikasi' => 'Kertas HVS A4 formulir sertifikat',
                    'volume' => 2,
                    'satuan' => 'Rim',
                    'harga_satuan' => 66200,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00029',
                    'nama_item' => 'Tinta/Toner Printer - Tinta Printer Epson 001 Black',
                    'spesifikasi' => 'Tinta Epson 001 Hitam original',
                    'volume' => 1,
                    'satuan' => 'Botol',
                    'harga_satuan' => 150000,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00029',
                    'nama_item' => 'Tinta/Toner Printer - Tinta Printer Epson 001 Color (Cyan/Magenta/Yellow)',
                    'spesifikasi' => 'Tinta Epson 001 Cyan',
                    'volume' => 1,
                    'satuan' => 'Botol',
                    'harga_satuan' => 130000,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00029',
                    'nama_item' => 'Tinta/Toner Printer - Tinta Printer Epson 001 Color (Cyan/Magenta/Yellow)',
                    'spesifikasi' => 'Tinta Epson 001 Magenta',
                    'volume' => 1,
                    'satuan' => 'Botol',
                    'harga_satuan' => 130000,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00029',
                    'nama_item' => 'Tinta/Toner Printer - Tinta Printer Epson 001 Color (Cyan/Magenta/Yellow)',
                    'spesifikasi' => 'Tinta Epson 001 Yellow',
                    'volume' => 1,
                    'satuan' => 'Botol',
                    'harga_satuan' => 130000,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00043',
                    'nama_item' => 'Makanan/Sembako - Kue Kotak isi 3 kue + air',
                    'spesifikasi' => 'Snack kotak pendampingan HKI',
                    'volume' => 36,
                    'satuan' => 'Kotak',
                    'harga_satuan' => 13300,
                ],
                [
                    'kode_rekening' => '5.1.02.02.001.00048',
                    'nama_item' => 'Beban Jasa Kontribusi Asosiasi - Kursus/Pelatihan/Bimtek',
                    'spesifikasi' => 'Kontribusi kegiatan bimtek/kursus HKI',
                    'volume' => 1,
                    'satuan' => 'Paket',
                    'harga_satuan' => 2500000,
                ],
            ],

            // =========================================================================
            // SUB KEGIATAN 3: 3.26.05.2.01.0006 (Total Pagu: Rp 18.272.100,00)
            // =========================================================================
            '3.26.05.2.01.0006' => [
                [
                    'kode_rekening' => '5.1.02.01.001.00024',
                    'nama_item' => 'Barang Cetakan - Kwitansi NCR, 5 ply',
                    'spesifikasi' => 'Buku kwitansi keuangan 5 rangkap',
                    'volume' => 3,
                    'satuan' => 'Pack',
                    'harga_satuan' => 46300,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00024',
                    'nama_item' => 'Map - Map Goby',
                    'spesifikasi' => 'Map arsip tebal goby',
                    'volume' => 10,
                    'satuan' => 'Buah',
                    'harga_satuan' => 33500,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00024',
                    'nama_item' => 'Alat Tulis - Pena Boldliner',
                    'spesifikasi' => 'Pena Boldliner hitam',
                    'volume' => 5,
                    'satuan' => 'Buah',
                    'harga_satuan' => 22200,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00024',
                    'nama_item' => 'Barang Cetakan - Fotocopy Biasa',
                    'spesifikasi' => 'Fotocopy modul pelatihan SDM ekraf',
                    'volume' => 1354,
                    'satuan' => 'Lembar',
                    'harga_satuan' => 300,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00024',
                    'nama_item' => 'Barang Cetakan - Fotocopy Biasa',
                    'spesifikasi' => 'Fotocopy perbup dan bahan KUA-PPAS',
                    'volume' => 7672,
                    'satuan' => 'Lembar',
                    'harga_satuan' => 300,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00025',
                    'nama_item' => 'Kertas HVS - F4, 75 gram (Pengadaan Khusus)',
                    'spesifikasi' => 'Kertas HVS F4 75 gr kegiatan pelatihan',
                    'volume' => 5,
                    'satuan' => 'Rim',
                    'harga_satuan' => 79400,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00025',
                    'nama_item' => 'Kertas HVS - F4, 75 gram (Pengadaan Khusus)',
                    'spesifikasi' => 'Kertas perbup KUA-PPAS',
                    'volume' => 3,
                    'satuan' => 'Kotak',
                    'harga_satuan' => 79400,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00052',
                    'nama_item' => 'Makanan - Nasi Bungkus 1 Protein',
                    'spesifikasi' => 'Makan rapat pembahasan perbup (20x10 kali)',
                    'volume' => 200,
                    'satuan' => 'Bungkus',
                    'harga_satuan' => 24000,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00052',
                    'nama_item' => 'Makanan/Sembako - Kue Kotak isi 3 kue + air',
                    'spesifikasi' => 'Kue kotak pembahasan perbup (20x10 kali)',
                    'volume' => 200,
                    'satuan' => 'Kotak',
                    'harga_satuan' => 13300,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00052',
                    'nama_item' => 'Makanan/Sembako - Kue Kotak isi 3 kue + air',
                    'spesifikasi' => 'Kue kotak pelatihan SDM ekraf',
                    'volume' => 274,
                    'satuan' => 'Kotak',
                    'harga_satuan' => 13300,
                ],
                [
                    'kode_rekening' => '5.1.02.01.001.00052',
                    'nama_item' => 'Makanan - Nasi Bungkus 1 Protein',
                    'spesifikasi' => 'Makan siang peserta pelatihan SDM ekraf',
                    'volume' => 135,
                    'satuan' => 'Bungkus',
                    'harga_satuan' => 24000,
                ],
            ],
        ];

        foreach ($tahunList as $thn) {
            foreach ($rkaDataBySubKegiatan as $kodeSub => $items) {
                $subKegiatan = MasterSubKegiatan::where('tahun_anggaran', $thn)
                    ->where('kode_sub_kegiatan', $kodeSub)
                    ->first();

                if (! $subKegiatan) {
                    continue;
                }

                foreach ($items as $item) {
                    $rek = $rekeningCache->get($item['kode_rekening']);
                    $barang = $barangCache->get($item['nama_item']);

                    RincianAnggaranBelanja::create([
                        'tahun_anggaran' => $thn,
                        'bidang_id' => $bidangId,
                        'sub_kegiatan_id' => $subKegiatan->id,
                        'kode_rekening_id' => $rek?->id ?? 1,
                        'master_barang_id' => $barang?->id,
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
                }
            }
        }

        $this->command->info('Seeder Rincian Anggaran Belanja (RKA) berhasil: Seluruh item belanja riil dari 3 PDF SIPD-RI Bidang Pemasaran Pariwisata telah diisi dengan presisi.');
    }
}

