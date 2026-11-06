<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\PengajuanSpj;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        ActivityLog::truncate();

        $pa = User::where('email', 'kepaladinas@gmail.com')->first();
        $ppk = User::where('email', 'sekretaris@gmail.com')->first();
        $bendahara = User::where('email', 'wilda@gmail.com')->first();
        $pptk = User::where('email', 'kabid@gmail.com')->first();
        $admin = User::where('email', 'doniaries@gmail.com')->first();

        $spjs = PengajuanSpj::with('kegiatan')->take(5)->get();

        $now = Carbon::now();

        // 1. Log Login
        if ($admin) {
            ActivityLog::create([
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'user_role' => 'Super Admin',
                'event' => 'login',
                'module' => 'Autentikasi',
                'description' => "Pengguna {$admin->name} berhasil login ke dalam sistem",
                'subject_type' => User::class,
                'subject_id' => $admin->id,
                'subject_label' => $admin->name,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
                'device' => 'Desktop (Windows • Chrome)',
                'created_at' => $now->copy()->subHours(8),
            ]);
        }

        if ($ppk) {
            ActivityLog::create([
                'user_id' => $ppk->id,
                'user_name' => $ppk->name,
                'user_role' => 'PPK (Kasubag Keuangan)',
                'event' => 'login',
                'module' => 'Autentikasi',
                'description' => "Pengguna {$ppk->name} berhasil login ke dalam sistem",
                'subject_type' => User::class,
                'subject_id' => $ppk->id,
                'subject_label' => $ppk->name,
                'ip_address' => '192.168.1.45',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
                'device' => 'Desktop (Windows • Chrome)',
                'created_at' => $now->copy()->subHours(6),
            ]);
        }

        // 2. Log Pengajuan SPJ
        foreach ($spjs as $index => $spj) {
            // Log Buat SPJ
            ActivityLog::create([
                'user_id' => $spj->pemohon_user_id ?: ($pptk?->id),
                'user_name' => $spj->pemohon?->name ?: ($pptk?->name ?? 'Staf Pelaksana'),
                'user_role' => 'PPTK (Kepala Bidang)',
                'event' => 'create',
                'module' => 'Pengajuan SPJ',
                'description' => "Membuat pengajuan SPJ baru nomor: {$spj->nomor_pengajuan} untuk keperluan: {$spj->keperluan}",
                'subject_type' => PengajuanSpj::class,
                'subject_id' => $spj->id,
                'subject_label' => $spj->nomor_pengajuan,
                'properties' => [
                    'nomor_pengajuan' => $spj->nomor_pengajuan,
                    'total_belanja' => (float) $spj->total_belanja,
                    'status' => $spj->status,
                ],
                'ip_address' => '192.168.1.1' . ($index + 2),
                'device' => 'Desktop (Windows • Chrome)',
                'created_at' => $now->copy()->subDays(2)->addHours($index),
            ]);

            // Log Verifikasi PPK
            if (in_array($spj->status, ['menunggu_persetujuan_pa', 'menunggu_verifikasi_bendahara', 'disetujui', 'dicairkan'], true) && $ppk) {
                ActivityLog::create([
                    'user_id' => $ppk->id,
                    'user_name' => $ppk->name,
                    'user_role' => 'PPK (Kasubag Keuangan)',
                    'event' => 'verify',
                    'module' => 'Pengajuan SPJ',
                    'description' => "Dokumen SPJ {$spj->nomor_pengajuan} diverifikasi & disetujui PPK, diteruskan ke PA",
                    'subject_type' => PengajuanSpj::class,
                    'subject_id' => $spj->id,
                    'subject_label' => $spj->nomor_pengajuan,
                    'properties' => [
                        'checklist' => '6 poin kepatuhan lulus',
                        'status' => 'menunggu_persetujuan_pa',
                    ],
                    'ip_address' => '192.168.1.45',
                    'device' => 'Desktop (Windows • Chrome)',
                    'created_at' => $now->copy()->subDays(1)->addHours($index),
                ]);
            }

            // Log Persetujuan PA
            if (in_array($spj->status, ['menunggu_verifikasi_bendahara', 'disetujui', 'dicairkan'], true) && $pa) {
                ActivityLog::create([
                    'user_id' => $pa->id,
                    'user_name' => $pa->name,
                    'user_role' => 'PA (Kepala Dinas)',
                    'event' => 'verify',
                    'module' => 'Pengajuan SPJ',
                    'description' => "Dokumen SPJ {$spj->nomor_pengajuan} disetujui & ditandatangani PA, diteruskan ke Bendahara Pengeluaran",
                    'subject_type' => PengajuanSpj::class,
                    'subject_id' => $spj->id,
                    'subject_label' => $spj->nomor_pengajuan,
                    'properties' => [
                        'status' => 'menunggu_verifikasi_bendahara',
                    ],
                    'ip_address' => '192.168.1.10',
                    'device' => 'Desktop (Windows • Chrome)',
                    'created_at' => $now->copy()->subHours(10)->addMinutes($index * 15),
                ]);
            }

            // Log Pencairan Bendahara
            if ($spj->status === 'dicairkan' && $bendahara) {
                ActivityLog::create([
                    'user_id' => $bendahara->id,
                    'user_name' => $bendahara->name,
                    'user_role' => 'Bendahara Pengeluaran',
                    'event' => 'cair',
                    'module' => 'Pengajuan SPJ',
                    'description' => "Dana SPJ {$spj->nomor_pengajuan} sebesar Rp " . number_format((float) ($spj->nominal_cair ?: $spj->total_belanja), 0, ',', '.') . " berhasil dicairkan oleh Bendahara",
                    'subject_type' => PengajuanSpj::class,
                    'subject_id' => $spj->id,
                    'subject_label' => $spj->nomor_pengajuan,
                    'properties' => [
                        'nominal_cair' => (float) ($spj->nominal_cair ?: $spj->total_belanja),
                        'status' => 'dicairkan',
                    ],
                    'ip_address' => '192.168.1.50',
                    'device' => 'Desktop (Windows • Chrome)',
                    'created_at' => $now->copy()->subHours(2),
                ]);
            }
        }

        // 3. Log Pengaturan Sistem
        if ($admin) {
            ActivityLog::create([
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'user_role' => 'Super Admin',
                'event' => 'update',
                'module' => 'Pengaturan',
                'description' => 'Memperbarui konfigurasi sistem aplikasi SAKTI: Sinkronisasi Identitas OPD dan Geofencing Kantor',
                'properties' => [
                    'parameter_diubah' => ['nama_instansi', 'radius_lokasi'],
                ],
                'ip_address' => '127.0.0.1',
                'device' => 'Desktop (Windows • Chrome)',
                'created_at' => $now->copy()->subHours(5),
            ]);
        }
    }
}

