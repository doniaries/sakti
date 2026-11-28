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

\Illuminate\Support\Facades\Artisan::command('presensi:pengingat {jenis=masuk}', function (string $jenis) {
    $today = now('Asia/Jakarta')->toDateString();
    $isLibur = \App\Models\HariLibur::whereDate('tanggal', $today)->exists();
    if ($isLibur) {
        $this->info('Hari libur, pengingat dilewati.');
        return;
    }

    $sudahAbsenIds = \App\Models\AbsensiHarian::whereDate('tanggal', $today)
        ->when($jenis === 'masuk', fn ($q) => $q->whereNotNull('jam_masuk'))
        ->when($jenis === 'pulang', fn ($q) => $q->whereNotNull('jam_pulang'))
        ->pluck('user_id');

    $sedangIzinIds = \App\Models\PengajuanIzin::where('status', 'disetujui')
        ->whereDate('tanggal_mulai', '<=', $today)
        ->whereDate('tanggal_selesai', '>=', $today)
        ->pluck('user_id');

    $targets = \App\Models\User::query()
        ->where('aktif', true)
        ->whereNotIn('id', $sudahAbsenIds->merge($sedangIzinIds)->unique())
        ->get();

    if ($targets->isEmpty()) {
        $this->info('Semua pegawai sudah presensi.');
        return;
    }

    $title = $jenis === 'pulang' ? 'Pengingat Absen Pulang' : 'Pengingat Absen Masuk';
    $body = $jenis === 'pulang'
        ? 'Jangan lupa melakukan presensi pulang sebelum meninggalkan kantor.'
        : 'Selamat pagi! Silakan lakukan presensi masuk hari ini.';

    \App\Services\PushNotificationService::sendToUsers($targets, $title, $body, ['url' => route('mobile.absensi')], 'info');
    $this->info("Pengingat {$jenis} dikirim ke {$targets->count()} pegawai.");
})->purpose('Kirim pengingat presensi masuk/pulang ke pegawai yang belum absen');

\Illuminate\Support\Facades\Schedule::command('presensi:pengingat masuk')->weekdays()->at('07:15')->timezone('Asia/Jakarta');
\Illuminate\Support\Facades\Schedule::command('presensi:pengingat pulang')->weekdays()->at('16:25')->timezone('Asia/Jakarta');

\Illuminate\Support\Facades\Artisan::command('agenda:pengingat', function () {
    $now = now('Asia/Jakarta');
    $windowEnd = $now->copy()->addMinutes(30);

    $agendas = \App\Models\AgendaKegiatan::query()
        ->whereNull('reminder_sent_at')
        ->whereDate('tanggal', '>=', $now->toDateString())
        ->whereDate('tanggal', '<=', $windowEnd->toDateString())
        ->with(['disposisis.user' => fn ($q) => $q->where('aktif', true)])
        ->get();

    $sentCount = 0;

    foreach ($agendas as $agenda) {
        $jamMulai = substr((string) $agenda->jam_mulai, 0, 5);
        $startAt = \Illuminate\Support\Carbon::parse(
            $agenda->tanggal->toDateString() . ' ' . $jamMulai,
            'Asia/Jakarta'
        );

        if ($startAt->lt($now) || $startAt->gt($windowEnd)) {
            continue;
        }

        $recipients = $agenda->disposisis
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->values();

        if ($recipients->isNotEmpty()) {
            \App\Services\PushNotificationService::sendToUsers(
                $recipients,
                'Pengingat Agenda (30 Menit Lagi)',
                "{$agenda->judul} pukul {$jamMulai} WIB di {$agenda->tempat}",
                ['url' => route('mobile.agenda')],
                'warning'
            );
            $sentCount++;
        }

        $agenda->update(['reminder_sent_at' => now()]);
    }

    $this->info("Pengingat agenda terkirim untuk {$sentCount} kegiatan.");
})->purpose('Kirim pengingat agenda 30 menit sebelum acara dimulai');

\Illuminate\Support\Facades\Schedule::command('agenda:pengingat')->everyMinute()->timezone('Asia/Jakarta')->withoutOverlapping();
