<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetDataRutinitas extends Command
{
    protected $signature = 'sakti:reset-rutinitas
                            {--dry-run : Tampilkan jumlah data yang akan dihapus tanpa menghapus}
                            {--force : Jalankan langsung tanpa konfirmasi interaktif}';

    protected $description = 'Kosongkan data transaksi rutinitas (Absensi, LHK, Izin, Surat Tugas, SPJ, Notifikasi, Log) tanpa menyentuh User, Pengaturan, Aset, Tim Kreatif, dan Master.';

    /**
     * Daftar tabel rutinitas/transaksi yang akan dikosongkan.
     *
     * @var array<int, string>
     */
    private array $routineTables = [
        'absensi_harian',
        'log_absensi',
        'lhk_pekerjaans',
        'lhks',
        'pengajuan_izins',
        'reputasis',
        'surat_tugas_user',
        'surat_tugas',
        'verifikasi_spj',
        'riwayat_status_spj',
        'detail_pengajuan_spj',
        'pengajuan_spj',
        'transaksi_barangs',
        'laporan_persediaans',
        'notifications',
        'activity_logs',
    ];

    public function handle(): int
    {
        $rows = [];
        $total = 0;

        foreach ($this->routineTables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            $count = DB::table($table)->count();
            $total += $count;
            $rows[] = [$table, $count];
        }

        $this->table(['Tabel Rutinitas', 'Jumlah Baris'], $rows);
        $this->info("Total baris data rutinitas: {$total}");
        $this->line('Data yang AMAN (tidak disentuh): users, roles, riwayat pegawai, pengaturan_aplikasis, buku_inventaris, berita_acara_bmds, bidang, jabatan, jadwal_kerjas, aktivitas, rencana_aksis, dan seluruh tabel master.');

        if ($this->option('dry-run')) {
            $this->comment('Mode --dry-run aktif: tidak ada data yang dihapus.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Yakin ingin mengosongkan seluruh tabel rutinitas di atas? Tindakan ini tidak dapat dibatalkan.')) {
            $this->warn('Dibatalkan.');

            return self::SUCCESS;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($this->routineTables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->info('Seluruh data rutinitas berhasil dikosongkan. Data User, Pengaturan, Aset, Tim Kreatif, dan Master tetap utuh.');

        return self::SUCCESS;
    }
}
