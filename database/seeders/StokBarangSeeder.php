<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\MasterBarang;
use App\Models\StokBarang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StokBarangSeeder extends Seeder
{
    /**
     * Seeder untuk membuat data stok awal untuk setiap barang di setiap bidang SAKTI.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        StokBarang::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $bidangs = Bidang::all();
        if ($bidangs->isEmpty()) {
            $this->command->error('Data bidang belum ada di SAKTI.');
            return;
        }

        $masterBarangs = MasterBarang::all();
        if ($masterBarangs->isEmpty()) {
            $this->command->error('Data master barang belum ada.');
            return;
        }

        $stokData = [];
        $count = 0;

        foreach ($bidangs as $bidang) {
            foreach ($masterBarangs as $barang) {
                $stokData[] = [
                    'bidang_id' => $bidang->id,
                    'master_barang_id' => $barang->id,
                    'stok_tersedia' => 10, // Stok awal sampel
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }
        }

        StokBarang::insert($stokData);

        $this->command->info("Data stok barang berhasil dibuat: {$count} data untuk {$bidangs->count()} bidang x {$masterBarangs->count()} barang.");
    }
}

