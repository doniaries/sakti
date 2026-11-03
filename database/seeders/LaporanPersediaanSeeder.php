<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\LaporanPersediaan;
use App\Models\MasterBarang;
use App\Models\TransaksiBarang;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaporanPersediaanSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        LaporanPersediaan::truncate();
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

        $today = Carbon::now();
        $bulanTahun = [
            [
                'bulan' => $today->month,
                'tahun' => $today->year,
            ],
            [
                'bulan' => $today->copy()->subMonth()->month,
                'tahun' => $today->copy()->subMonth()->year,
            ],
        ];

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($bidangs as $bidang) {
                foreach ($masterBarangs as $barang) {
                    $saldoAwal = 0;

                    foreach ($bulanTahun as $periode) {
                        $transaksi = TransaksiBarang::where([
                            'bidang_id' => $bidang->id,
                            'master_barang_id' => $barang->id,
                        ])
                            ->whereYear('tanggal_transaksi', $periode['tahun'])
                            ->whereMonth('tanggal_transaksi', $periode['bulan'])
                            ->get();

                        $jumlahMasuk = $transaksi->where('jenis_transaksi', 'masuk')->sum('jumlah');
                        $nilaiMasuk = $transaksi->where('jenis_transaksi', 'masuk')->sum('nilai_barang');
                        $jumlahKeluar = $transaksi->where('jenis_transaksi', 'keluar')->sum('jumlah');
                        $nilaiKeluar = $transaksi->where('jenis_transaksi', 'keluar')->sum('nilai_barang');

                        $saldoAkhir = $saldoAwal + $jumlahMasuk - $jumlahKeluar;
                        $nilaiAkhir = $saldoAkhir * (float) $barang->harga_satuan;

                        LaporanPersediaan::create([
                            'bidang_id' => $bidang->id,
                            'master_barang_id' => $barang->id,
                            'bulan' => $periode['bulan'],
                            'tahun' => $periode['tahun'],
                            'saldo_awal' => $saldoAwal,
                            'jumlah_masuk' => $jumlahMasuk,
                            'nilai_masuk' => $nilaiMasuk,
                            'jumlah_keluar' => $jumlahKeluar,
                            'nilai_keluar' => $nilaiKeluar,
                            'jumlah_expired' => 0,
                            'nilai_expired' => 0,
                            'saldo_akhir' => $saldoAkhir,
                            'nilai_akhir' => $nilaiAkhir,
                        ]);

                        $saldoAwal = $saldoAkhir;
                        $count++;
                    }
                }
            }

            DB::commit();
            $this->command->info("Laporan Persediaan berhasil dibuat: {$count} data.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Gagal membuat laporan persediaan: ' . $e->getMessage());
        }
    }
}

