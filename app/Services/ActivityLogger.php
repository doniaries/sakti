<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Catat log aktivitas pengguna di aplikasi.
     *
     * @param string $event Jenis aksi (login, logout, create, update, delete, verify, revisi, cair, absen, dll.)
     * @param string $module Nama modul (Autentikasi, Pengajuan SPJ, Presensi, LHK, Kepegawaian, Pengaturan, dll.)
     * @param string $description Rincian tindakan yang dilakukan user
     * @param Model|null $subject Objek model terkait (misal instance PengajuanSpj, User, Lhk)
     * @param array $properties Data tambahan (payload, catatan, perubahan nilai)
     * @param User|null $user Pengguna pelaku tindakan (default: user yang sedang login)
     */
    public static function log(
        string $event,
        string $module,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?User $user = null
    ): ?ActivityLog {
        try {
            /** @var User|null $actingUser */
            $actingUser = $user ?? Auth::user();

            $userName = $actingUser?->name ?? 'Sistem / Anonim';
            $userRole = self::resolveUserRoleLabel($actingUser);

            $subjectType = $subject ? get_class($subject) : null;
            $subjectId = $subject?->getKey();
            $subjectLabel = self::resolveSubjectLabel($subject);

            $ip = request()->ip();
            $userAgent = request()->userAgent();
            $device = self::resolveDeviceName($userAgent);

            return ActivityLog::create([
                'user_id' => $actingUser?->id,
                'user_name' => $userName,
                'user_role' => $userRole,
                'event' => strtolower($event),
                'module' => $module,
                'description' => $description,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'subject_label' => $subjectLabel,
                'properties' => ! empty($properties) ? $properties : null,
                'ip_address' => $ip,
                'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
                'device' => $device,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Jangan sampai kegagalan logging menghentikan transaksi utama
            report($e);
            return null;
        }
    }

    /**
     * Tentukan label peran pengguna saat melakukan aksi.
     */
    protected static function resolveUserRoleLabel(?User $user): string
    {
        if (! $user) {
            return 'Guest / Sistem';
        }

        if ($user->hasRole('super_admin')) {
            return 'Super Admin';
        }

        $anggaranRole = $user->anggaran_role instanceof \App\Enums\AnggaranRole
            ? $user->anggaran_role->value
            : $user->anggaran_role;

        if ($anggaranRole === 'PA' || $user->hasRole('pa')) {
            return 'PA (Kepala Dinas)';
        }

        if ($anggaranRole === 'PPK' || $user->hasRole('ppk')) {
            return 'PPK (Kasubag Keuangan)';
        }

        if ($anggaranRole === 'BENDAHARA' || $user->hasRole('bendahara')) {
            return 'Bendahara Pengeluaran';
        }

        if ($anggaranRole === 'PPTK' || $user->hasRole('pptk')) {
            return 'PPTK (Kepala Bidang)';
        }

        if ($user->hasRole('admin')) {
            return 'Admin Dinas';
        }

        if ($user->hasRole('jf')) {
            return 'Jabatan Fungsional (JF)';
        }

        return 'Pegawai';
    }

    /**
     * Tentukan label identitas objek subject yang dapat dibaca manusia.
     */
    protected static function resolveSubjectLabel(?Model $subject): ?string
    {
        if (! $subject) {
            return null;
        }

        if (isset($subject->nomor_pengajuan)) {
            return (string) $subject->nomor_pengajuan;
        }

        if (isset($subject->name)) {
            return (string) $subject->name;
        }

        if (isset($subject->nama)) {
            return (string) $subject->nama;
        }

        if (isset($subject->nama_kegiatan)) {
            return (string) $subject->nama_kegiatan;
        }

        if (isset($subject->tanggal)) {
            return (string) $subject->tanggal;
        }

        return class_basename($subject) . ' #' . $subject->getKey();
    }

    /**
     * Sederhanakan deteksi perangkat dari User Agent.
     */
    protected static function resolveDeviceName(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Sistem / Background Job';
        }

        $isMobile = preg_match('/(android|iphone|ipad|mobile)/i', $userAgent);
        $os = 'Komputer';

        if (preg_match('/windows/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/android/i', $userAgent)) {
            $os = 'Android';
        } elseif (preg_match('/iphone|ipad/i', $userAgent)) {
            $os = 'iOS / Apple';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $os = 'Linux';
        }

        $browser = 'Browser';
        if (preg_match('/chrome|crios/i', $userAgent) && ! preg_match('/edg/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/edg/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/firefox|fxios/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $userAgent) && ! preg_match('/chrome/i', $userAgent)) {
            $browser = 'Safari';
        }

        return ($isMobile ? 'Mobile' : 'Desktop') . " ({$os} • {$browser})";
    }
}

