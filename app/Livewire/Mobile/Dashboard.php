<?php

namespace App\Livewire\Mobile;

use App\Models\AbsensiHarian;
use App\Models\Lhk;
use App\Models\User;
use App\Services\AttendancePunchService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.mobile')]
#[Title('Dashboard - Absensi LHK Mobile')]
class Dashboard extends Component
{
    use WithFileUploads;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public mixed $new_avatar = null;

    /**
     * Upload dan ganti foto profil langsung dari dashboard
     * Otomatis membersihkan foto lama dari storage agar server tidak penuh
     */
    public function updatedNewAvatar()
    {
        $this->validate([
            'new_avatar' => 'required|image|max:2048',
        ], [
            'new_avatar.image' => 'File harus berupa gambar (JPG, PNG, WEBP).',
            'new_avatar.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        /** @var User $freshUser */
        $freshUser = User::findOrFail(Auth::id());

        $oldAvatar = $freshUser->avatar_url;
        if (!empty($oldAvatar) && Storage::disk('public')->exists($oldAvatar)) {
            Storage::disk('public')->delete($oldAvatar);
        }

        $path = $this->new_avatar->store('avatars', 'public');
        $freshUser->update(['avatar_url' => $path]);
        $this->new_avatar = null;

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Foto Profil Berhasil Diganti!',
            'message' => 'Foto profil terbaru telah disimpan dan file lama telah dihapus dari server.',
        ]);
    }

    /**
     * Ambil presensi masuk atau pulang langsung dari kartu dashboard
     */
    #[On('trigger-punch')]
    public function ambilAbsen(string $jenis = 'auto', $lat = null, $lng = null)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $result = AttendancePunchService::punch(
            $user,
            $jenis,
            $lat !== null ? (float) $lat : null,
            $lng !== null ? (float) $lng : null
        );

        $this->dispatch('notify', [
            'type' => $result['type'] ?? ($result['success'] ? 'success' : 'error'),
            'title' => $result['title'] ?? 'Presensi Kehadiran',
            'message' => $result['message'] ?? '',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('mobile.login');
    }

    public function markNotificationAsRead(string $id): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $user->unreadNotifications()->where('id', $id)->update(['read_at' => now()]);
        $this->dispatch('notif-count-updated', count: $user->unreadNotifications()->count());
    }

    public function markAllNotificationsAsRead(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $user->unreadNotifications()->update(['read_at' => now()]);
        $this->dispatch('notif-count-updated', count: 0);
    }

    public function clearAllNotifications(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $user->notifications()->delete();
        $this->dispatch('notif-count-updated', count: 0);
    }

    public function render()
    {
        /** @var User $user */
        $user = User::with(['jabatan', 'bidang', 'atasan', 'jadwalKerja'])->find(Auth::id()) ?? Auth::user();
        $today = Carbon::today();
        
        // Data hari ini
        $absensiHariIni = AbsensiHarian::where('user_id', $user->id)
            ->where('tanggal', $today->format('Y-m-d'))
            ->first();
        $hariLiburHariIni = \App\Models\HariLibur::where('tanggal', $today->format('Y-m-d'))
            ->where('is_aktif', true)
            ->first();

        $sudahMasuk = $absensiHariIni && !empty($absensiHariIni->jam_masuk);
        $sudahPulang = $absensiHariIni && !empty($absensiHariIni->jam_pulang);

        // Parameter Jadwal Kerja & Batas Waktu
        $jadwal = $user->getJadwalKerja() ?? \App\Models\JadwalKerja::getDefault();
        $bukaMasuk = $jadwal ? substr($jadwal->buka_absen_masuk, 0, 5) : '06:00';
        $tutupMasuk = $jadwal ? substr($jadwal->tutup_absen_masuk, 0, 5) : '09:00';
        $jamPulangResmi = $jadwal ? $jadwal->getJamPulangHari($today->format('Y-m-d')) : '16:00';
        $tutupPulang = $jadwal ? substr($jadwal->tutup_absen_pulang, 0, 5) : '23:59';
        $pengaturan = \App\Models\PengaturanAplikasi::getPengaturan();
        $modeSimulasi = (bool) ($pengaturan->mode_simulasi || session('mode_simulasi_absensi', false));
        $nowTime = ($modeSimulasi && ! empty($pengaturan->simulasi_jam_aktif))
            ? substr($pengaturan->simulasi_jam_aktif, 0, 5)
            : Carbon::now()->format('H:i');

        $isAdmin = $user->hasAnyRole(['super_admin', 'admin']);
        $belumBukaMasuk = (!$isAdmin && $nowTime < $bukaMasuk);
        $lewatBatasMasuk = (!$isAdmin && !$sudahMasuk && $nowTime > $tutupMasuk);
        $belumJamPulang = (!$isAdmin && $nowTime < $jamPulangResmi);
        $lewatBatasPulang = (!$isAdmin && $sudahMasuk && !$sudahPulang && $nowTime > $tutupPulang);

        $absensi7Hari = AbsensiHarian::query()
            ->where('user_id', $user->id)
            ->whereBetween('tanggal', [$today->copy()->subDays(6)->format('Y-m-d'), $today->format('Y-m-d')])
            ->get()
            ->keyBy(fn (AbsensiHarian $a) => \Illuminate\Support\Carbon::parse($a->tanggal)->format('Y-m-d'));

        $riwayat7Hari = [];
        for ($i = 0; $i <= 6; $i++) {
            $date = $today->copy()->subDays($i);
            $absen = $absensi7Hari->get($date->format('Y-m-d'));

            $riwayat7Hari[] = [
                'hari' => $date->translatedFormat('l'),
                'tanggal' => $date->translatedFormat('d M Y'),
                'status' => $absen?->status,
                'jam_masuk' => $absen?->jam_masuk,
                'jam_pulang' => $absen?->jam_pulang,
            ];
        }

        $unreadNotifCount = $user->unreadNotifications()->count();
        $recentNotifications = $user->notifications()->latest()->limit(15)->get();

        // Hitung Jam Kerja Efektif ASN (Standar 7j 30m/hari atau 37j 30m/minggu, non-istirahat)
        $menitHariIni = $absensiHariIni ? $absensiHariIni->hitungMenitKerjaEfektif() : 0;
        $allBulanIni = AbsensiHarian::where('user_id', $user->id)
            ->whereMonth('tanggal', $today->month)
            ->whereYear('tanggal', $today->year)
            ->get();
        $totalMenitBulanIni = (int) $allBulanIni->sum(fn (AbsensiHarian $a) => $a->hitungMenitKerjaEfektif());
        $targetBulananAsn = AbsensiHarian::hitungTargetBulananAsn($today->month, $today->year);
        $persenBulanIni = $targetBulananAsn['target_menit'] > 0
            ? min(100, (int) round(($totalMenitBulanIni / $targetBulananAsn['target_menit']) * 100))
            : 0;

        $pengaturan = \App\Models\PengaturanAplikasi::getPengaturan();
        $tema = request()->query('tema', $pengaturan->portal_theme ?? 'default');

        return view($tema === 'rhoe' ? 'livewire.mobile.dashboard-rhoe' : 'livewire.mobile.dashboard', [
            'user' => $user,
            'hariLiburHariIni' => $hariLiburHariIni,
            'unreadNotifCount' => $unreadNotifCount,
            'recentNotifications' => $recentNotifications,
            'jamKerjaAsn' => [
                'harian_menit' => $menitHariIni,
                'persen_harian' => min(100, (int) round(($menitHariIni / max(1, AbsensiHarian::TARGET_MENIT_HARIAN_ASN)) * 100)),
                'harian_format' => AbsensiHarian::formatDurasiJamMenit($menitHariIni),
                'target_harian_format' => AbsensiHarian::formatDurasiJamMenit(AbsensiHarian::TARGET_MENIT_HARIAN_ASN),
                'target_mingguan_format' => AbsensiHarian::formatDurasiJamMenit(AbsensiHarian::TARGET_MENIT_MINGGUAN_ASN),
                'bulanan_menit' => $totalMenitBulanIni,
                'bulanan_format' => AbsensiHarian::formatDurasiJamMenit($totalMenitBulanIni),
                'target_bulanan_format' => $targetBulananAsn['target_format'],
                'hari_kerja_bulan' => $targetBulananAsn['hari_kerja'],
                'persen_bulanan' => $persenBulanIni,
            ],
            'hariIni' => [
                'sudah_masuk' => $sudahMasuk,
                'sudah_pulang' => $sudahPulang,
                'jam_masuk' => $absensiHariIni->jam_masuk ?? null,
                'jam_pulang' => $absensiHariIni->jam_pulang ?? null,
                'status' => $absensiHariIni->status ?? null,
                'buka_masuk' => $bukaMasuk,
                'tutup_masuk' => $tutupMasuk,
                'jam_pulang_resmi' => $jamPulangResmi,
                'tutup_pulang' => $tutupPulang,
                'belum_buka_masuk' => $belumBukaMasuk,
                'lewat_batas_masuk' => $lewatBatasMasuk,
                'belum_jam_pulang' => $belumJamPulang,
                'lewat_batas_pulang' => $lewatBatasPulang,
                'is_admin' => $isAdmin,
            ],
            'riwayat7Hari' => $riwayat7Hari,
        ]);
    }
}
