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

        $kodeAtk = KodeRekening::where('uraian', 'ALAT TULIS KANTOR')->first();
        $kodeKomputer = KodeRekening::where('uraian', 'PERALATAN KOMPUTER')->first();
        $kodePerlengkapan = KodeRekening::where('uraian', 'PERLENGKAPAN KANTOR')->first();
        $kodeCetakan = KodeRekening::where('uraian', 'BAHAN CETAKAN')->first();

        if (!$kodeAtk || !$kodeKomputer || !$kodePerlengkapan || !$kodeCetakan) {
            $this->command->error('Kode rekening tidak lengkap. Jalankan KodeRekeningSeeder terlebih dahulu.');
            return;
        }

        $satuanBuah = Satuan::firstOrCreate(['nama' => 'Buah']);
        $satuanRim = Satuan::firstOrCreate(['nama' => 'Rim']);
        $satuanPack = Satuan::firstOrCreate(['nama' => 'Pack']);
        $satuanBotol = Satuan::firstOrCreate(['nama' => 'Botol']);
        $satuanUnit = Satuan::firstOrCreate(['nama' => 'Unit']);
        $satuanBox = Satuan::firstOrCreate(['nama' => 'Box']);

        $masterBarangData = [
            // ALAT TULIS KANTOR
            [
                'kode_rekening_id' => $kodeAtk->id,
                'kode_barang' => '1.1.7.01.01.01.001',
                'uraian' => $kodeAtk->uraian,
                'nama_barang' => 'Pena Signo Uniball',
                'satuan_id' => $satuanBuah->id,
                'harga_satuan' => 23000,
                'keterangan' => 'Pena Signo warna hitam'
            ],
            [
                'kode_rekening_id' => $kodeAtk->id,
                'kode_barang' => '1.1.7.01.01.01.002',
                'uraian' => $kodeAtk->uraian,
                'nama_barang' => 'Kertas HVS A4 80 gram',
                'satuan_id' => $satuanRim->id,
                'harga_satuan' => 55000,
                'keterangan' => 'Kertas HVS A4 merk Sinar Dunia'
            ],
            [
                'kode_rekening_id' => $kodeAtk->id,
                'kode_barang' => '1.1.7.01.01.01.003',
                'uraian' => $kodeAtk->uraian,
                'nama_barang' => 'Spidol Whiteboard',
                'satuan_id' => $satuanPack->id,
                'harga_satuan' => 75000,
                'keterangan' => 'Spidol whiteboard merk Snowman isi 12'
            ],
            [
                'kode_rekening_id' => $kodeAtk->id,
                'kode_barang' => '1.1.7.01.01.01.004',
                'uraian' => $kodeAtk->uraian,
                'nama_barang' => 'Tinta Printer',
                'satuan_id' => $satuanBotol->id,
                'harga_satuan' => 85000,
                'keterangan' => 'Tinta printer Epson warna hitam'
            ],

            // PERALATAN KOMPUTER
            [
                'kode_rekening_id' => $kodeKomputer->id,
                'kode_barang' => '1.1.7.01.02.01.001',
                'uraian' => $kodeKomputer->uraian,
                'nama_barang' => 'Flashdisk 32GB',
                'satuan_id' => $satuanBuah->id,
                'harga_satuan' => 95000,
                'keterangan' => 'Flashdisk merk Sandisk'
            ],
            [
                'kode_rekening_id' => $kodeKomputer->id,
                'kode_barang' => '1.1.7.01.02.01.002',
                'uraian' => $kodeKomputer->uraian,
                'nama_barang' => 'Mouse Wireless',
                'satuan_id' => $satuanUnit->id,
                'harga_satuan' => 150000,
                'keterangan' => 'Mouse wireless merk Logitech'
            ],
            [
                'kode_rekening_id' => $kodeKomputer->id,
                'kode_barang' => '1.1.7.01.02.01.003',
                'uraian' => $kodeKomputer->uraian,
                'nama_barang' => 'Keyboard Mechanical',
                'satuan_id' => $satuanUnit->id,
                'harga_satuan' => 550000,
                'keterangan' => 'Keyboard mechanical gaming'
            ],
            [
                'kode_rekening_id' => $kodeKomputer->id,
                'kode_barang' => '1.1.7.01.02.01.004',
                'uraian' => $kodeKomputer->uraian,
                'nama_barang' => 'Harddisk External 1TB',
                'satuan_id' => $satuanUnit->id,
                'harga_satuan' => 850000,
                'keterangan' => 'Harddisk external merk WD 1TB'
            ],

            // PERLENGKAPAN KANTOR
            [
                'kode_rekening_id' => $kodePerlengkapan->id,
                'kode_barang' => '1.1.7.01.03.01.001',
                'uraian' => $kodePerlengkapan->uraian,
                'nama_barang' => 'Kursi Kerja',
                'satuan_id' => $satuanUnit->id,
                'harga_satuan' => 750000,
                'keterangan' => 'Kursi kerja ergonomis'
            ],
            [
                'kode_rekening_id' => $kodePerlengkapan->id,
                'kode_barang' => '1.1.7.01.03.01.002',
                'uraian' => $kodePerlengkapan->uraian,
                'nama_barang' => 'Lemari Arsip',
                'satuan_id' => $satuanUnit->id,
                'harga_satuan' => 1500000,
                'keterangan' => 'Lemari arsip 2 pintu'
            ],
            [
                'kode_rekening_id' => $kodePerlengkapan->id,
                'kode_barang' => '1.1.7.01.03.01.003',
                'uraian' => $kodePerlengkapan->uraian,
                'nama_barang' => 'Papan Tulis',
                'satuan_id' => $satuanUnit->id,
                'harga_satuan' => 450000,
                'keterangan' => 'Papan tulis whiteboard 120x80cm'
            ],
            [
                'kode_rekening_id' => $kodePerlengkapan->id,
                'kode_barang' => '1.1.7.01.03.01.004',
                'uraian' => $kodePerlengkapan->uraian,
                'nama_barang' => 'Dispenser Air',
                'satuan_id' => $satuanUnit->id,
                'harga_satuan' => 350000,
                'keterangan' => 'Dispenser air panas dan dingin'
            ],

            // BAHAN CETAKAN
            [
                'kode_rekening_id' => $kodeCetakan->id,
                'kode_barang' => '1.1.7.01.04.01.001',
                'uraian' => $kodeCetakan->uraian,
                'nama_barang' => 'Kop Surat',
                'satuan_id' => $satuanRim->id,
                'harga_satuan' => 150000,
                'keterangan' => 'Kop surat dinas'
            ],
            [
                'kode_rekening_id' => $kodeCetakan->id,
                'kode_barang' => '1.1.7.01.04.01.002',
                'uraian' => $kodeCetakan->uraian,
                'nama_barang' => 'Amplop Dinas',
                'satuan_id' => $satuanBox->id,
                'harga_satuan' => 75000,
                'keterangan' => 'Amplop dinas ukuran A4'
            ],
            [
                'kode_rekening_id' => $kodeCetakan->id,
                'kode_barang' => '1.1.7.01.04.01.003',
                'uraian' => $kodeCetakan->uraian,
                'nama_barang' => 'Kartu Nama',
                'satuan_id' => $satuanBox->id,
                'harga_satuan' => 100000,
                'keterangan' => 'Kartu nama dinas isi 100'
            ],
            [
                'kode_rekening_id' => $kodeCetakan->id,
                'kode_barang' => '1.1.7.01.04.01.004',
                'uraian' => $kodeCetakan->uraian,
                'nama_barang' => 'Stempel Dinas',
                'satuan_id' => $satuanBuah->id,
                'harga_satuan' => 120000,
                'keterangan' => 'Stempel dinas otomatis'
            ],
        ];

        foreach ($masterBarangData as $data) {
            MasterBarang::create($data);
        }

        $this->command->info('Data master barang berhasil disimpan: ' . count($masterBarangData) . ' barang');
    }
}
