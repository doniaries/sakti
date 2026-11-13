<?php

namespace Database\Seeders;

use App\Models\JenisIzin;
use App\Models\PengajuanIzin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PengajuanIzinSeeder extends Seeder
{
    /**
     * Seed data Pengajuan Izin / Sakit / Cuti / WFH yang sinkron dengan AbsensiHarian
     * dan otomatis memicu notifikasi realtime melalui PengajuanIzinObserver.
     */
    public function run(): void
    {
        $izinPribadi = JenisIzin::where('kategori', 'izin')->first() ?? JenisIzin::first();
        $izinSakit = JenisIzin::where('kategori', 'sakit')->first() ?? $izinPribadi;
        $izinCuti = JenisIzin::where('kategori', 'cuti')->first() ?? $izinPribadi;
        $izinWfh = JenisIzin::where('kategori', 'wfh')->first() ?? $izinPribadi;

        if (! $izinPribadi) {
            $this->command?->warn('JenisIzin belum tersedia. Jalankan JenisIzinSeeder terlebih dahulu.');
            return;
        }

        $approver = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['super_admin', 'admin', 'admin_kepegawaian']))
            ->where('aktif', true)
            ->first() ?? User::first();

        $doni = User::where('email', 'doniaries@gmail.com')->first();
        $pegawaiLain = User::where('aktif', true)
            ->whereNotIn('email', ['superadmin@gmail.com', 'doniaries@gmail.com'])
            ->whereHas('roles', fn ($q) => $q->where('name', 'pegawai'))
            ->limit(3)
            ->get();

        // Cari hari kerja terdekat untuk tanggal simulasi yang konsisten
        $weekdayKemarin = Carbon::today()->subDay();
        while ($weekdayKemarin->isWeekend()) {
            $weekdayKemarin->subDay();
        }

        $weekdayBesok = Carbon::today()->addDay();
        while ($weekdayBesok->isWeekend()) {
            $weekdayBesok->addDay();
        }

        $scenarios = [];

        if ($doni) {
            // 1. Pengajuan WFH menunggu persetujuan -> notif ke super_admin, admin, kepegawaian, & atasan
            $scenarios[] = [
                'user_id' => $doni->id,
                'jenis_izin_id' => $izinWfh->id,
                'tanggal_mulai' => $weekdayBesok->format('Y-m-d'),
                'tanggal_selesai' => $weekdayBesok->format('Y-m-d'),
                'alasan' => 'Penyusunan laporan kinerja dan koordinasi daring lintas bidang.',
                'target_status' => 'menunggu',
                'disetujui_oleh' => null,
                'catatan_atasan' => null,
            ];

            // 2. Pengajuan Izin Pribadi disetujui -> sinkron ke AbsensiHarian + notif ke doniaries@gmail.com
            $scenarios[] = [
                'user_id' => $doni->id,
                'jenis_izin_id' => $izinPribadi->id,
                'tanggal_mulai' => $weekdayKemarin->format('Y-m-d'),
                'tanggal_selesai' => $weekdayKemarin->format('Y-m-d'),
                'alasan' => 'Keperluan urusan keluarga yang mendesak.',
                'target_status' => 'disetujui',
                'disetujui_oleh' => $doni->atasan_id ?? $approver?->id,
                'catatan_atasan' => 'Disetujui, harap tetap memantau pekerjaan bidang.',
            ];
        }

        if ($pegawaiLain->isNotEmpty()) {
            $p1 = $pegawaiLain->get(0);
            if ($p1) {
                $scenarios[] = [
                    'user_id' => $p1->id,
                    'jenis_izin_id' => $izinSakit->id,
                    'tanggal_mulai' => Carbon::today()->isWeekday() ? Carbon::today()->format('Y-m-d') : $weekdayKemarin->format('Y-m-d'),
                    'tanggal_selesai' => Carbon::today()->isWeekday() ? Carbon::today()->format('Y-m-d') : $weekdayKemarin->format('Y-m-d'),
                    'alasan' => 'Demam dan istirahat sesuai surat keterangan dokter.',
                    'target_status' => 'menunggu',
                    'disetujui_oleh' => null,
                    'catatan_atasan' => null,
                ];
            }

            $p2 = $pegawaiLain->get(1);
            if ($p2) {
                $scenarios[] = [
                    'user_id' => $p2->id,
                    'jenis_izin_id' => $izinCuti->id,
                    'tanggal_mulai' => $weekdayBesok->format('Y-m-d'),
                    'tanggal_selesai' => $weekdayBesok->copy()->addDay()->isWeekday()
                        ? $weekdayBesok->copy()->addDay()->format('Y-m-d')
                        : $weekdayBesok->format('Y-m-d'),
                    'alasan' => 'Pengambilan hak cuti tahunan.',
                    'target_status' => 'menunggu',
                    'disetujui_oleh' => null,
                    'catatan_atasan' => null,
                ];
            }

            $p3 = $pegawaiLain->get(2);
            if ($p3) {
                $scenarios[] = [
                    'user_id' => $p3->id,
                    'jenis_izin_id' => $izinPribadi->id,
                    'tanggal_mulai' => $weekdayKemarin->format('Y-m-d'),
                    'tanggal_selesai' => $weekdayKemarin->format('Y-m-d'),
                    'alasan' => 'Izin keperluan pribadi mendadak.',
                    'target_status' => 'ditolak',
                    'disetujui_oleh' => $p3->atasan_id ?? $approver?->id,
                    'catatan_atasan' => 'Mohon jadwalkan ulang karena ada agenda pelayanan prioritas.',
                ];
            }
        }

        $createdCount = 0;

        foreach ($scenarios as $data) {
            $exists = PengajuanIzin::where('user_id', $data['user_id'])
                ->where('jenis_izin_id', $data['jenis_izin_id'])
                ->whereDate('tanggal_mulai', $data['tanggal_mulai'])
                ->exists();

            if ($exists) {
                continue;
            }

            // Buat dengan status 'menunggu' agar Observer::created mengirim notifikasi pengajuan baru
            $izin = PengajuanIzin::create([
                'user_id' => $data['user_id'],
                'jenis_izin_id' => $data['jenis_izin_id'],
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'alasan' => $data['alasan'],
                'status' => 'menunggu',
            ]);

            // Jika target status disetujui/ditolak, update agar Observer::updated & sinkronisasi AbsensiHarian berjalan
            if ($data['target_status'] !== 'menunggu') {
                $izin->update([
                    'status' => $data['target_status'],
                    'disetujui_oleh' => $data['disetujui_oleh'],
                    'catatan_atasan' => $data['catatan_atasan'],
                ]);
            }

            $createdCount++;
        }

        $this->command?->info("Seeder Pengajuan Izin selesai: {$createdCount} pengajuan baru dibuat, tersinkron ke AbsensiHarian & notifikasi.");
    }
}

