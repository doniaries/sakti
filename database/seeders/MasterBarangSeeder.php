<?php

namespace Database\Seeders;

use App\Models\KodeRekening;
use App\Models\MasterBarang;
use App\Models\Satuan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterBarangSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterBarang::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $kodeAtk = KodeRekening::where('kode_lengkap', '5.1.02.01.001.00024')
            ->orWhere('uraian', 'LIKE', '%Alat Tulis Kantor%')
            ->orWhere('uraian', 'ALAT TULIS KANTOR')
            ->first();

        $kodeKomputer = KodeRekening::where('kode_lengkap', '5.1.02.01.001.00029')
            ->orWhere('uraian', 'LIKE', '%Bahan Komputer%')
            ->orWhere('uraian', 'LIKE', '%Komputer%')
            ->first();

        $kodePerlengkapan = KodeRekening::where('kode_lengkap', '5.1.02.01.001.00030')
            ->orWhere('kode_lengkap', '5.1.02.01.001.00025')
            ->orWhere('uraian', 'LIKE', '%Perabot%')
            ->orWhere('uraian', 'LIKE', '%Kertas%')
            ->first();

        $kodeCetakan = KodeRekening::where('kode_lengkap', '5.1.02.01.001.00026')
            ->orWhere('kode_lengkap', '5.1.02.01.001.00025')
            ->orWhere('uraian', 'LIKE', '%Cetak%')
            ->first();

        // Fallback cerdas agar seeder tidak pernah macet
        $defaultKode = KodeRekening::first();
        $kodeAtk = $kodeAtk ?: $defaultKode;
        $kodeKomputer = $kodeKomputer ?: $defaultKode;
        $kodePerlengkapan = $kodePerlengkapan ?: $defaultKode;
        $kodeCetakan = $kodeCetakan ?: $defaultKode;

        if (! $kodeAtk) {
            $this->command->error('Kode rekening belum tersedia di database. Jalankan KodeRekeningSeeder terlebih dahulu.');
            return;
        }

        // SATUAN LENGKAP
        $satuanBuah = Satuan::firstOrCreate(['nama' => 'Buah']);
        $satuanRim = Satuan::firstOrCreate(['nama' => 'Rim']);
        $satuanPack = Satuan::firstOrCreate(['nama' => 'Pack']);
        $satuanBotol = Satuan::firstOrCreate(['nama' => 'Botol']);
        $satuanUnit = Satuan::firstOrCreate(['nama' => 'Unit']);
        $satuanBox = Satuan::firstOrCreate(['nama' => 'Box']);
        $satuanLiter = Satuan::firstOrCreate(['nama' => 'Liter']);
        $satuanLembar = Satuan::firstOrCreate(['nama' => 'Lembar']);
        $satuanLusin = Satuan::firstOrCreate(['nama' => 'Lusin']);
        $satuanKotak = Satuan::firstOrCreate(['nama' => 'Kotak']);
        $satuanKg = Satuan::firstOrCreate(['nama' => 'Kg']);
        $satuanBungkus = Satuan::firstOrCreate(['nama' => 'Bungkus']);
        $satuanOrang = Satuan::firstOrCreate(['nama' => 'Orang']);
        $satuanOrangKali = Satuan::firstOrCreate(['nama' => 'Orang / Kali']);

        // KODE REKENING DPA 2027
        $kodeBbm = KodeRekening::firstOrCreate(
            ['kode_lengkap' => '5.1.02.01.001.00004'],
            ['uraian' => 'Belanja Bahan-Bahan Bakar dan Pelumas', 'sub_sub_rincian_objek' => '000']
        );
        $kodeMaminRapat = KodeRekening::firstOrCreate(
            ['kode_lengkap' => '5.1.02.01.001.00052'],
            ['uraian' => 'Belanja Makanan dan Minuman Rapat', 'sub_sub_rincian_objek' => '000']
        );
        $kodeMaminLapangan = KodeRekening::firstOrCreate(
            ['kode_lengkap' => '5.1.02.01.001.00058'],
            ['uraian' => 'Belanja Makanan dan Minuman Aktivitas Lapangan', 'sub_sub_rincian_objek' => '000']
        );
        $kodeJasaSeni = KodeRekening::firstOrCreate(
            ['kode_lengkap' => '5.1.02.02.001.00025'],
            ['uraian' => 'Belanja Jasa Tenaga Kesenian dan Kebudayaan', 'sub_sub_rincian_objek' => '000']
        );
        $kodePerjadin = KodeRekening::firstOrCreate(
            ['kode_lengkap' => '5.1.02.04.001.00003'],
            ['uraian' => 'Belanja Perjalanan Dinas Dalam Kota', 'sub_sub_rincian_objek' => '000']
        );

        $masterBarangData = [
            // ALAT TULIS KANTOR
            [
                'kode_rekening_id' => $kodeAtk->id,
                'kode_barang' => '1.1.7.01.01.01.001',
                'uraian' => $kodeAtk->uraian,
                'nama_barang' => 'Pena Signo Uniball',
                'satuan_id' => $satuanBuah->id,
                'keterangan' => 'Pena Signo warna hitam'
            ],
            [
                'kode_rekening_id' => $kodeAtk->id,
                'kode_barang' => '1.1.7.01.01.01.002',
                'uraian' => $kodeAtk->uraian,
                'nama_barang' => 'Kertas HVS A4 80 gram',
                'satuan_id' => $satuanRim->id,
                'keterangan' => 'Kertas HVS A4 merk Sinar Dunia'
            ],
            [
                'kode_rekening_id' => $kodeAtk->id,
                'kode_barang' => '1.1.7.01.01.01.003',
                'uraian' => $kodeAtk->uraian,
                'nama_barang' => 'Spidol Whiteboard',
                'satuan_id' => $satuanPack->id,
                'keterangan' => 'Spidol whiteboard merk Snowman isi 12'
            ],
            [
                'kode_rekening_id' => $kodeAtk->id,
                'kode_barang' => '1.1.7.01.01.01.004',
                'uraian' => $kodeAtk->uraian,
                'nama_barang' => 'Tinta Printer',
                'satuan_id' => $satuanBotol->id,
                'keterangan' => 'Tinta printer Epson warna hitam'
            ],

            // PERALATAN KOMPUTER
            [
                'kode_rekening_id' => $kodeKomputer->id,
                'kode_barang' => '1.1.7.01.02.01.001',
                'uraian' => $kodeKomputer->uraian,
                'nama_barang' => 'Flashdisk 32GB',
                'satuan_id' => $satuanBuah->id,
                'keterangan' => 'Flashdisk merk Sandisk'
            ],
            [
                'kode_rekening_id' => $kodeKomputer->id,
                'kode_barang' => '1.1.7.01.02.01.002',
                'uraian' => $kodeKomputer->uraian,
                'nama_barang' => 'Mouse Wireless',
                'satuan_id' => $satuanUnit->id,
                'keterangan' => 'Mouse wireless merk Logitech'
            ],
            [
                'kode_rekening_id' => $kodeKomputer->id,
                'kode_barang' => '1.1.7.01.02.01.003',
                'uraian' => $kodeKomputer->uraian,
                'nama_barang' => 'Keyboard Mechanical',
                'satuan_id' => $satuanUnit->id,
                'keterangan' => 'Keyboard mechanical gaming'
            ],
            [
                'kode_rekening_id' => $kodeKomputer->id,
                'kode_barang' => '1.1.7.01.02.01.004',
                'uraian' => $kodeKomputer->uraian,
                'nama_barang' => 'Harddisk External 1TB',
                'satuan_id' => $satuanUnit->id,
                'keterangan' => 'Harddisk external merk WD 1TB'
            ],

            // PERLENGKAPAN KANTOR
            [
                'kode_rekening_id' => $kodePerlengkapan->id,
                'kode_barang' => '1.1.7.01.03.01.001',
                'uraian' => $kodePerlengkapan->uraian,
                'nama_barang' => 'Kursi Kerja',
                'satuan_id' => $satuanUnit->id,
                'keterangan' => 'Kursi kerja ergonomis'
            ],
            [
                'kode_rekening_id' => $kodePerlengkapan->id,
                'kode_barang' => '1.1.7.01.03.01.002',
                'uraian' => $kodePerlengkapan->uraian,
                'nama_barang' => 'Lemari Arsip',
                'satuan_id' => $satuanUnit->id,
                'keterangan' => 'Lemari arsip 2 pintu'
            ],
            [
                'kode_rekening_id' => $kodePerlengkapan->id,
                'kode_barang' => '1.1.7.01.03.01.003',
                'uraian' => $kodePerlengkapan->uraian,
                'nama_barang' => 'Papan Tulis',
                'satuan_id' => $satuanUnit->id,
                'keterangan' => 'Papan tulis whiteboard 120x80cm'
            ],
            [
                'kode_rekening_id' => $kodePerlengkapan->id,
                'kode_barang' => '1.1.7.01.03.01.004',
                'uraian' => $kodePerlengkapan->uraian,
                'nama_barang' => 'Dispenser Air',
                'satuan_id' => $satuanUnit->id,
                'keterangan' => 'Dispenser air panas dan dingin'
            ],

            // BAHAN CETAKAN
            [
                'kode_rekening_id' => $kodeCetakan->id,
                'kode_barang' => '1.1.7.01.04.01.001',
                'uraian' => $kodeCetakan->uraian,
                'nama_barang' => 'Kop Surat',
                'satuan_id' => $satuanRim->id,
                'keterangan' => 'Kop surat dinas'
            ],
            [
                'kode_rekening_id' => $kodeCetakan->id,
                'kode_barang' => '1.1.7.01.04.01.002',
                'uraian' => $kodeCetakan->uraian,
                'nama_barang' => 'Amplop Dinas',
                'satuan_id' => $satuanBox->id,
                'keterangan' => 'Amplop dinas ukuran A4'
            ],
            [
                'kode_rekening_id' => $kodeCetakan->id,
                'kode_barang' => '1.1.7.01.04.01.003',
                'uraian' => $kodeCetakan->uraian,
                'nama_barang' => 'Kartu Nama',
                'satuan_id' => $satuanBox->id,
                'keterangan' => 'Kartu nama dinas isi 100'
            ],
            [
                'kode_rekening_id' => $kodeCetakan->id,
                'kode_barang' => '1.1.7.01.04.01.004',
                'uraian' => $kodeCetakan->uraian,
                'nama_barang' => 'Stempel Dinas',
                'satuan_id' => $satuanBuah->id,
                'keterangan' => 'Stempel dinas otomatis'
            ],

            // =========================================================================
            // DPA TAHUN 2027 (STANDAR SSH / SIPD KAB. SIJUNJUNG)
            // =========================================================================
            // 1. BBM & PELUMAS (5.1.02.01.001.00004)
            [
                'kode_rekening_id' => $kodeBbm->id,
                'kode_barang' => '1.1.7.01.01.01.001',
                'uraian' => $kodeBbm->uraian,
                'nama_barang' => 'BBM - Pertamax',
                'satuan_id' => $satuanLiter->id,
                'keterangan' => 'Bahan Bakar Minyak - Pertamax (Liter)'
            ],
            // 2. ATK & CETAKAN TAMBAHAN (5.1.02.01.001.00024)
            [
                'kode_rekening_id' => $kodeAtk->id,
                'kode_barang' => '1.1.7.01.01.01.002',
                'uraian' => $kodeAtk->uraian,
                'nama_barang' => 'Barang Cetakan - Fotocopy Biasa',
                'satuan_id' => $satuanLembar->id,
                'keterangan' => 'Barang Cetakan - Fotocopy Biasa'
            ],
            [
                'kode_rekening_id' => $kodeAtk->id,
                'kode_barang' => '1.1.7.01.01.01.003',
                'uraian' => $kodeAtk->uraian,
                'nama_barang' => 'Kertas HVS Sidu, ukuran F4, 75 gram',
                'satuan_id' => $satuanRim->id,
                'keterangan' => 'Kertas HVS Sidu, ukuran F4, 75 gram'
            ],
            [
                'kode_rekening_id' => $kodeAtk->id,
                'kode_barang' => '1.1.7.01.01.01.004',
                'uraian' => $kodeAtk->uraian,
                'nama_barang' => 'Alat Tulis - Pena My-Gel',
                'satuan_id' => $satuanLusin->id,
                'keterangan' => 'Alat Tulis - Pena My-Gel (Lusin)'
            ],
            // 3. BAHAN KOMPUTER (5.1.02.01.001.00029)
            [
                'kode_rekening_id' => $kodeKomputer->id,
                'kode_barang' => '1.1.7.01.02.01.005',
                'uraian' => $kodeKomputer->uraian,
                'nama_barang' => 'Tinta/Toner Printer - Cartridge Printer Brother LC',
                'satuan_id' => $satuanBuah->id,
                'keterangan' => 'Tinta/Toner Printer - Cartridge Printer Brother LC'
            ],
            // 4. MAKANAN & MINUMAN RAPAT (5.1.02.01.001.00052)
            [
                'kode_rekening_id' => $kodeMaminRapat->id,
                'kode_barang' => '1.1.7.01.05.01.001',
                'uraian' => $kodeMaminRapat->uraian,
                'nama_barang' => 'Makanan - Kue Kotak isi 2 kue + air',
                'satuan_id' => $satuanKotak->id,
                'keterangan' => 'Makanan - Kue Kotak isi 2 kue + air'
            ],
            [
                'kode_rekening_id' => $kodeMaminRapat->id,
                'kode_barang' => '1.1.7.01.05.01.002',
                'uraian' => $kodeMaminRapat->uraian,
                'nama_barang' => 'Makanan - Kue Kotak isi 3 kue + air',
                'satuan_id' => $satuanKotak->id,
                'keterangan' => 'Makanan - Kue Kotak isi 3 kue + air'
            ],
            [
                'kode_rekening_id' => $kodeMaminRapat->id,
                'kode_barang' => '1.1.7.01.05.01.003',
                'uraian' => $kodeMaminRapat->uraian,
                'nama_barang' => 'Bahan Masakan/Minuman/Kue - Kopi Cap Rangkiang',
                'satuan_id' => $satuanKg->id,
                'keterangan' => 'Bahan Masakan/Minuman/Kue - Kopi Cap Rangkiang'
            ],
            [
                'kode_rekening_id' => $kodeMaminRapat->id,
                'kode_barang' => '1.1.7.01.05.01.004',
                'uraian' => $kodeMaminRapat->uraian,
                'nama_barang' => 'Bahan Masakan/Minuman/Kue - Gula Pasir',
                'satuan_id' => $satuanKg->id,
                'keterangan' => 'Bahan Masakan/Minuman/Kue - Gula Pasir'
            ],
            [
                'kode_rekening_id' => $kodeMaminRapat->id,
                'kode_barang' => '1.1.7.01.05.01.005',
                'uraian' => $kodeMaminRapat->uraian,
                'nama_barang' => 'Makanan - Nasi Bungkus 1 Protein',
                'satuan_id' => $satuanBungkus->id,
                'keterangan' => 'Makanan - Nasi Bungkus 1 Protein Rapat'
            ],
            // 5. MAKANAN & MINUMAN LAPANGAN (5.1.02.01.001.00058)
            [
                'kode_rekening_id' => $kodeMaminLapangan->id,
                'kode_barang' => '1.1.7.01.06.01.001',
                'uraian' => $kodeMaminLapangan->uraian,
                'nama_barang' => 'Makanan - Nasi Goreng Lapangan',
                'satuan_id' => $satuanBungkus->id,
                'keterangan' => 'Makanan - Nasi Goreng Lapangan'
            ],
            // 6. JASA TENAGA KESENIAN (5.1.02.02.001.00025)
            [
                'kode_rekening_id' => $kodeJasaSeni->id,
                'kode_barang' => '1.1.7.02.01.01.001',
                'uraian' => $kodeJasaSeni->uraian,
                'nama_barang' => 'Beban Jasa Tenaga Kesenian dan Kebudayaan - Upah Pekerja Seni',
                'satuan_id' => $satuanOrang->id,
                'keterangan' => 'Upah Pekerja Seni 120.000 per orang'
            ],
            // 7. PERJALANAN DINAS DALAM KOTA (5.1.02.04.001.00003)
            [
                'kode_rekening_id' => $kodePerjadin->id,
                'kode_barang' => '1.1.7.04.01.01.001',
                'uraian' => $kodePerjadin->uraian,
                'nama_barang' => 'Beban Perjalanan Dinas Dalam Kota - Transport Darat Sijunjung (56.000)',
                'satuan_id' => $satuanOrangKali->id,
                'keterangan' => 'Biaya Transportasi Darat Dalam Kota Sijunjung Rp 56.000'
            ],
            [
                'kode_rekening_id' => $kodePerjadin->id,
                'kode_barang' => '1.1.7.04.01.01.002',
                'uraian' => $kodePerjadin->uraian,
                'nama_barang' => 'Beban Perjalanan Dinas Dalam Kota - Transport Darat Sijunjung (84.000)',
                'satuan_id' => $satuanOrangKali->id,
                'keterangan' => 'Biaya Transportasi Darat Dalam Kota Sijunjung Rp 84.000'
            ],
            [
                'kode_rekening_id' => $kodePerjadin->id,
                'kode_barang' => '1.1.7.04.01.01.003',
                'uraian' => $kodePerjadin->uraian,
                'nama_barang' => 'Beban Perjalanan Dinas Dalam Kota - Transport Darat Sijunjung (140.000)',
                'satuan_id' => $satuanOrangKali->id,
                'keterangan' => 'Biaya Transportasi Darat Dalam Kota Sijunjung Rp 140.000'
            ],
            [
                'kode_rekening_id' => $kodePerjadin->id,
                'kode_barang' => '1.1.7.04.01.01.004',
                'uraian' => $kodePerjadin->uraian,
                'nama_barang' => 'Beban Perjalanan Dinas Dalam Kota - Transport Darat Sijunjung (49.000)',
                'satuan_id' => $satuanOrangKali->id,
                'keterangan' => 'Biaya Transportasi Darat Dalam Kota Sijunjung Rp 49.000'
            ],
            [
                'kode_rekening_id' => $kodePerjadin->id,
                'kode_barang' => '1.1.7.04.01.01.005',
                'uraian' => $kodePerjadin->uraian,
                'nama_barang' => 'Beban Perjalanan Dinas Dalam Kota - Transport Darat Sijunjung (70.000)',
                'satuan_id' => $satuanOrangKali->id,
                'keterangan' => 'Biaya Transportasi Darat Dalam Kota Sijunjung Rp 70.000'
            ],
            [
                'kode_rekening_id' => $kodePerjadin->id,
                'kode_barang' => '1.1.7.04.01.01.006',
                'uraian' => $kodePerjadin->uraian,
                'nama_barang' => 'Beban Perjalanan Dinas Dalam Kota - Transport Darat Sijunjung (35.000)',
                'satuan_id' => $satuanOrangKali->id,
                'keterangan' => 'Biaya Transportasi Darat Dalam Kota Sijunjung Rp 35.000'
            ],
        ];

        foreach ($masterBarangData as $data) {
            MasterBarang::create($data);
        }

        $this->command->info('Data master barang berhasil disimpan: ' . count($masterBarangData) . ' barang');
    }
}

