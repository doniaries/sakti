<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\MasterBarang;
use App\Models\StokBarang;
use App\Models\TransaksiBarang;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransaksiBarangSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TransaksiBarang::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $bidangs = Bidang::take(2)->get();
        if ($bidangs->isEmpty()) {
            $this->command->error('Bidang belum ada.');
            return;
        }

        $masterBarangs = MasterBarang::take(4)->get();
        if ($masterBarangs->isEmpty()) {
            $this->command->error('Master barang belum ada.');
            return;
        }

        $admin = User::first();
        $today = Carbon::now();

        $bulanTahun = [
            [
                'bulan' => $today->month,
                'tahun' => $today->year,
                'tanggal_awal' => $today->copy()->startOfMonth()->addDays(4),
                'tanggal_keluar' => $today->copy()->startOfMonth()->addDays(14),
            ],
            [
                'bulan' => $today->copy()->subMonth()->month,
                'tahun' => $today->copy()->subMonth()->year,
                'tanggal_awal' => $today->copy()->subMonth()->startOfMonth()->addDays(4),
                'tanggal_keluar' => $today->copy()->subMonth()->startOfMonth()->addDays(14),
            ],
        ];

        DB::beginTransaction();
        try {
            foreach ($bidangs as $bidang) {
                foreach ($masterBarangs as $barang) {
                    foreach ($bulanTahun as $periode) {
                        // 1. Transaksi Masuk
                        $jumlahMasuk = 20;
                        $nilaiMasuk = $jumlahMasuk * (float) $barang->harga_satuan;

                        TransaksiBarang::create([
                            'bidang_id' => $bidang->id,
                            'master_barang_id' => $barang->id,
                            'jenis_transaksi' => 'masuk',
                            'jumlah' => $jumlahMasuk,
                            'harga_satuan' => $barang->harga_satuan,
                            'nilai_barang' => $nilaiMasuk,
                            'tanggal_transaksi' => $periode['tanggal_awal']->format('Y-m-d'),
                            'keterangan' => "Penerimaan {$barang->nama_barang} pengadaan persediaan",
                            'created_by' => $admin?->id,
                        ]);

                        // 2. Transaksi Keluar
                        $jumlahKeluar = 5;
                        $nilaiKeluar = $jumlahKeluar * (float) $barang->harga_satuan;

                        TransaksiBarang::create([
                            'bidang_id' => $bidang->id,
                            'master_barang_id' => $barang->id,
                            'jenis_transaksi' => 'keluar',
                            'jumlah' => $jumlahKeluar,
                            'harga_satuan' => $barang->harga_satuan,
                            'nilai_barang' => $nilaiKeluar,
                            'tanggal_transaksi' => $periode['tanggal_keluar']->format('Y-m-d'),
                            'keterangan' => "Distribusi pemakaian operasional {$bidang->nama}",
                            'created_by' => $admin?->id,
                        ]);
                    }
                }
            }

            DB::commit();
            $this->command->info('Data transaksi persediaan berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Gagal membuat transaksi: ' . $e->getMessage());
        }
    }
}

