<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BukuInventaris;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class BukuInventarisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/buku_inventaris.json');

        if (! File::exists($jsonPath)) {
            $this->command?->error("File data {$jsonPath} tidak ditemukan!");
            return;
        }

        $items = json_decode(File::get($jsonPath), true);

        if (! is_array($items)) {
            $this->command?->error("Format JSON pada {$jsonPath} tidak valid!");
            return;
        }

        $now = now();
        $chunks = array_chunk($items, 100);

        BukuInventaris::truncate();

        foreach ($chunks as $chunk) {
            $dataToInsert = array_map(function ($row) use ($now) {
                return array_merge($row, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }, $chunk);

            BukuInventaris::insert($dataToInsert);
        }

        $totalCount = BukuInventaris::count();
        $totalHarga = (float) BukuInventaris::sum('harga');

        $this->command?->info("Berhasil mengimpor {$totalCount} aset ke Buku Inventaris Gabungan.");
        $this->command?->info("Total Nilai Inventaris: Rp " . number_format($totalHarga, 2, ',', '.'));
    }
}
