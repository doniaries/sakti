<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\KodeRekening;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportKodeRekeningCommand extends Command
{
    protected $signature = 'sakti:import-kode-rekening {--fresh : Kosongkan tabel sebelum import}';

    protected $description = 'Import 3.283+ data kode rekening belanja standar SIPD RI dari file CSV/SQL';

    public function handle(): int
    {
        $csvPath = database_path('seeders/data/kode_rekening_belanja.csv');

        if (! file_exists($csvPath)) {
            $this->error("File CSV tidak ditemukan di: {$csvPath}");

            return self::FAILURE;
        }

        $this->info('Memulai import kode rekening belanja SIPD RI...');

        if ($this->option('fresh')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            KodeRekening::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->warn('Tabel kode_rekenings telah dikosongkan.');
        }

        $handle = fopen($csvPath, 'r');
        if (! $handle) {
            $this->error('Gagal membuka file CSV.');

            return self::FAILURE;
        }

        // Header
        $headers = fgetcsv($handle);
        $batch = [];
        $batchSize = 250;
        $totalImported = 0;

        $bar = $this->output->createProgressBar();
        $bar->start();

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row) || count($row) < 9) {
                continue;
            }

            $data = [
                'akun' => $row[0] ?: '5',
                'kelompok' => $row[1] ?? '',
                'jenis' => $row[2] ?? '',
                'objek' => $row[3] ?? '',
                'rincian_objek' => $row[4] ?? '',
                'sub_rincian_objek' => $row[5] ?? '',
                'sub_sub_rincian_objek' => $row[6] ?? '',
                'kode_lengkap' => $row[7],
                'uraian' => $row[8],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $batch[] = $data;

            if (count($batch) >= $batchSize) {
                KodeRekening::upsert(
                    $batch,
                    ['kode_lengkap'],
                    ['uraian', 'akun', 'kelompok', 'jenis', 'objek', 'rincian_objek', 'sub_rincian_objek', 'updated_at']
                );
                $totalImported += count($batch);
                $bar->advance(count($batch));
                $batch = [];
            }
        }

        if (! empty($batch)) {
            KodeRekening::upsert(
                $batch,
                ['kode_lengkap'],
                ['uraian', 'akun', 'kelompok', 'jenis', 'objek', 'rincian_objek', 'sub_rincian_objek', 'updated_at']
            );
            $totalImported += count($batch);
            $bar->advance(count($batch));
        }

        fclose($handle);
        $bar->finish();
        $this->newLine();

        $this->info("Berhasil mengimpor/memperbarui {$totalImported} data kode rekening belanja!");

        return self::SUCCESS;
    }
}

