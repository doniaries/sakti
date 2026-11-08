<?php

namespace App\Console\Commands;

use App\Services\Rka\SipdRkaImporterService;
use Illuminate\Console\Command;

class ImportSipdRkaPdfCommand extends Command
{
    protected $signature = 'sakti:import-rka-pdf {path : Path absolut atau relatif ke file PDF RKA SIPD}';

    protected $description = 'Import data RKA dari file PDF resmi SIPD RI ke dalam tabel rincian_anggaran_belanja';

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return 1;
        }

        $this->info("Memulai parsing dokumen PDF SIPD: {$path}...");

        try {
            $result = SipdRkaImporterService::import($path);

            $this->newLine();
            $this->info('=== IMPORT RKA SIPD BERHASIL ===');
            $this->line("Sub Kegiatan  : [{$result['kode_sub_kegiatan']}] {$result['sub_kegiatan']}");
            $this->line("Item Diimpor  : {$result['imported_items']} item");
            $this->line("Item Dilewati : {$result['skipped_items']} item (Pagu Rp 0)");
            $this->line("Total Pagu    : Rp " . number_format($result['total_pagu'], 0, ',', '.'));
            $this->newLine();

            return 0;
        } catch (\Throwable $e) {
            $this->error('Gagal import RKA: ' . $e->getMessage());
            return 1;
        }
    }
}

