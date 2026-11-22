<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Artisan::command('cuti:carry-over', function () {
    \App\Models\User::query()->chunkById(100, function ($users) {
        foreach ($users as $user) {
            $sisaTahunLalu = min(6, max(0, (int) ($user->sisa_cuti ?? 0)));
            $user->update(['sisa_cuti' => 12 + $sisaTahunLalu]);
        }
    });
    $this->info('Carry-over sisa cuti tahunan (maks 6 hari + 12 hari baru) selesai.');
})->purpose('Hitung sisa cuti tahunan ASN ke tahun berikutnya (maks 6 hari terbawa, total maks 18 hari)');

\Illuminate\Support\Facades\Schedule::command('cuti:carry-over')->yearlyOn(1, 1, '00:05');

\Illuminate\Support\Facades\Artisan::command('simulasi:cleanup', function () {
    $deletedAbsensi = \Illuminate\Support\Facades\DB::table('absensi_harian')
        ->where('keterangan', 'Simulasi 6 Bulan')
        ->delete();
    $deletedIzin = \Illuminate\Support\Facades\DB::table('pengajuan_izins')
        ->where('alasan', 'Simulasi 6 Bulan - Keperluan Pribadi')
        ->delete();
    $this->info("Cleanup selesai: {$deletedAbsensi} absensi_harian & {$deletedIzin} pengajuan_izin dihapus.");
})->purpose('Hapus seluruh data simulasi 6 bulan dengan aman');
