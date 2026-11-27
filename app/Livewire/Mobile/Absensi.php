<?php

namespace App\Livewire\Mobile;

use App\Models\AbsensiHarian;
use App\Models\PengaturanAplikasi;
use App\Models\User;
use App\Services\AttendancePunchService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Absensi - Absensi LHK Mobile')]
class Absensi extends Component
{
    public float|string|null $latitude = null;
    public float|string|null $longitude = null;
    public bool $modeSimulasi = false;
    public ?string $simulasiJamAktif = null;

    public function updatedSimulasiJamAktif(?string $value): void
    {
        $this->setSimulasiJam($value);
    }

    public function setSimulasiJam(?string $jam = null): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $this->modeSimulasi || ! $user || ! $user->hasAnyRole(['super_admin', 'superadmin'])) {
            return;
        }

        $this->simulasiJamAktif = $jam ?: null;
        PengaturanAplikasi::first()?->update([
            'simulasi_jam_aktif' => $this->simulasiJamAktif,
        ]);
    }

    public function mount()
    {
        /** @var User|null $user */
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasAnyRole(['super_admin', 'superadmin']);
        $pengaturan = PengaturanAplikasi::getPengaturan();
        $this->modeSimulasi = $isSuperAdmin && (bool) ($pengaturan->mode_simulasi || session('mode_simulasi_absensi', false));
        $this->simulasiJamAktif = $isSuperAdmin ? ($pengaturan->simulasi_jam_aktif ?: null) : null;
    }

    /**
     * Presensi Masuk Spesifik (dari Tombol Absen Masuk)
     */
    public function punchMasuk($lat = null, $lng = null)
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user->hasAnyRole(['super_admin', 'admin']);
        $bypass = $this->modeSimulasi;

        $result = AttendancePunchService::punch(
            $user,
            'masuk',
            $lat !== null ? (float) $lat : null,
            $lng !== null ? (float) $lng : null,
            $bypass
        );

        $this->dispatch('notify', [
            'type' => $result['type'] ?? ($result['success'] ? 'success' : 'error'),
            'title' => $result['title'] ?? 'Presensi Masuk',
            'message' => $result['message'] ?? '',
        ]);
    }

    /**
     * Presensi Keluar/Pulang Spesifik (dari Tombol Absen Keluar)
     */
    public function punchPulang($lat = null, $lng = null)
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user->hasAnyRole(['super_admin', 'admin']);
        $bypass = $this->modeSimulasi;

        $result = AttendancePunchService::punch(
            $user,
            'pulang',
            $lat !== null ? (float) $lat : null,
            $lng !== null ? (float) $lng : null,
            $bypass
        );

        $this->dispatch('notify', [
            'type' => $result['type'] ?? ($result['success'] ? 'success' : 'error'),
            'title' => $result['title'] ?? 'Presensi Keluar',
            'message' => $result['message'] ?? '',
        ]);
    }

    /**
     * Presensi Otomatis (Masuk jika belum, Pulang jika sudah masuk) - Trigger Navbar Center Button
     */
    #[On('trigger-punch')]
    public function punch($lat = null, $lng = null, bool $force = false)
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user->hasAnyRole(['super_admin', 'admin']);
        $bypass = $this->modeSimulasi || ($isAdmin && $force);

        $result = AttendancePunchService::punch(
            $user,
            'auto',
            $lat !== null ? (float) $lat : null,
            $lng !== null ? (float) $lng : null,
            $bypass
        );

        $this->dispatch('notify', [
            'type' => $result['type'] ?? ($result['success'] ? 'success' : 'error'),
            'title' => $result['title'] ?? 'Presensi Kehadiran',
            'message' => $result['message'] ?? '',
            'show_user' => (bool) ($result['success'] ?? false),
        ]);
    }

    /**
     * Toggle Mode Simulasi untuk Admin
     */
    public function toggleModeSimulasi(): void
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['super_admin', 'superadmin'])) {
            return;
        }

        $this->modeSimulasi = !$this->modeSimulasi;
        PengaturanAplikasi::first()?->update(['mode_simulasi' => $this->modeSimulasi]);
        session(['mode_simulasi_absensi' => $this->modeSimulasi]);

        $statusStr = $this->modeSimulasi ? 'DIAKTIFKAN (Bebas Lokasi, Jam Normal)' : 'DINONAKTIFKAN (Ikuti Lokasi & Jam Asli)';
        $this->dispatch('notify', [
            'type' => 'info',
            'title' => 'Mode Simulasi ' . ($this->modeSimulasi ? 'Aktif' : 'Nonaktif'),
            'message' => "Mode pengujian telah {$statusStr}.",
        ]);
    }

    /**
     * Fitur Admin: Reset Data Hari Ini untuk Pengujian Ulang
     */
    public function simulasiResetMasuk(): void
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['super_admin', 'superadmin'])) {
            return;
        }

        $todayStr = Carbon::today()->format('Y-m-d');

        \App\Models\LogAbsensi::where('user_id', $user->id)
            ->whereDate('waktu_absensi', $todayStr)
            ->delete();

        AbsensiHarian::where('user_id', $user->id)
            ->where('tanggal', $todayStr)
            ->delete();

        $this->modeSimulasi = true;

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Data Direset Bersih',
            'message' => 'Status hari ini dikosongkan. Anda dapat langsung menekan tombol Absen Masuk.',
        ]);
    }

    /**
     * Fitur Admin: Simulasi State "Sudah Masuk Pagi, Siap Absen Pulang"
     */
    public function simulasiSiapPulang(): void
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['super_admin', 'superadmin'])) {
            return;
        }

        $todayStr = Carbon::today()->format('Y-m-d');
        $pengaturan = PengaturanAplikasi::getPengaturan();
        $lat = (float) ($pengaturan->latitude_kantor ?? -0.69752300);
        $lng = (float) ($pengaturan->longitude_kantor ?? 100.98547200);

        \App\Models\LogAbsensi::where('user_id', $user->id)
            ->where('jenis', 'pulang')
            ->whereDate('waktu_absensi', $todayStr)
            ->delete();

        $logMasuk = \App\Models\LogAbsensi::updateOrCreate(
            [
                'user_id' => $user->id,
                'jenis' => 'masuk',
                'waktu_absensi' => "{$todayStr} 07:25:00",
            ],
            [
                'nomor_absensi' => (string) ($user->nomor_absensi ?? $user->id),
                'perangkat' => 'mobile',
                'metode_verifikasi' => 'gps',
                'sumber' => 'Mobile PWA',
                'data_asli' => [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'status_lokasi' => 'dalam_radius',
                ],
            ]
        );

        AbsensiHarian::updateOrCreate(
            [
                'user_id' => $user->id,
                'tanggal' => $todayStr,
            ],
            [
                'status' => 'hadir',
                'log_masuk_id' => $logMasuk->id,
                'jam_masuk' => '07:25:00',
                'latitude_masuk' => $lat,
                'longitude_masuk' => $lng,
                'log_pulang_id' => null,
                'jam_pulang' => null,
                'latitude_pulang' => null,
                'longitude_pulang' => null,
                'is_absen_mobile' => true,
            ]
        );

        $this->modeSimulasi = true;

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Simulasi Masuk Siap',
            'message' => 'Absen masuk terpasang (07:25 WIB). Sekarang Anda dapat menekan tombol Absen Keluar.',
        ]);
    }

    /**
     * Fitur Admin: Set titik koordinat kantor ke posisi GPS saat ini
     */
    public function setLokasiKantorKeSaya(float|string|null $lat, float|string|null $lng): void
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['super_admin', 'superadmin'])) {
            return;
        }

        $pengaturan = PengaturanAplikasi::getPengaturan();
        $pengaturan->update([
            'latitude_kantor' => $lat,
            'longitude_kantor' => $lng,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Titik Kantor Diperbarui!',
            'message' => 'Koordinat kantor berhasil diset ke titik Anda (' . round($lat, 6) . ', ' . round($lng, 6) . ').',
        ]);
    }

    public function render()
    {
        $pengaturan = \App\Models\PengaturanAplikasi::getPengaturan();
        $this->modeSimulasi = (bool) ($pengaturan->mode_simulasi || session('mode_simulasi_absensi', false));
        $this->simulasiJamAktif = $pengaturan->simulasi_jam_aktif ?: null;

        /** @var User $user */
        $user = User::with(['jabatan', 'bidang', 'jadwalKerja'])->find(Auth::id()) ?? Auth::user();
        $isAdmin = $user->hasAnyRole(['super_admin', 'admin']);
        $today = Carbon::today()->format('Y-m-d');
        $nowTime = ($this->modeSimulasi && ! empty($pengaturan->simulasi_jam_aktif))
            ? substr($pengaturan->simulasi_jam_aktif, 0, 5)
            : Carbon::now()->format('H:i');
        $isJumat = Carbon::today()->dayOfWeekIso === 5;

        $absenHarian = AbsensiHarian::where('user_id', $user->id)->where('tanggal', $today)->first();
        $hariLiburHariIni = \App\Models\HariLibur::where('tanggal', $today)->where('is_aktif', true)->first();

        $sudahMasuk = $absenHarian && !empty($absenHarian->jam_masuk);
        $sudahPulang = $absenHarian && !empty($absenHarian->jam_pulang);
        $selesai = $sudahMasuk && $sudahPulang;

        // Parameter Jam Kerja
        $jadwal = $user->getJadwalKerja() ?? \App\Models\JadwalKerja::getDefault();
        $bukaMasuk = $jadwal ? substr($jadwal->buka_absen_masuk, 0, 5) : '06:00';
        $tutupMasuk = $jadwal ? substr($jadwal->tutup_absen_masuk, 0, 5) : '09:00';
        $jamMasukResmi = $jadwal ? $jadwal->getJamMasukHari($today) : '07:30';
        $jamPulangResmi = $jadwal ? $jadwal->getJamPulangHari($today) : '16:00';
        $tutupPulang = $jadwal ? substr($jadwal->tutup_absen_pulang, 0, 5) : '23:59';
        $toleransi = $jadwal ? (int) $jadwal->toleransi_terlambat_menit : 15;
        $batasToleransi = Carbon::parse("$today $jamMasukResmi:00")->addMinutes($toleransi)->format('H:i');

        $lewatBatasMasuk = (!$sudahMasuk && $nowTime > $tutupMasuk);
        $belumBukaMasuk = ($nowTime < $bukaMasuk);
        $belumJamPulang = ($nowTime < $jamPulangResmi);
        $lewatBatasPulang = ($nowTime > $tutupPulang);

        $menitKerjaHariIni = $absenHarian ? $absenHarian->hitungMenitKerjaEfektif() : 0;

        return view('livewire.mobile.absensi', [
            'user' => $user,
            'absenHarian' => $absenHarian,
            'menitKerjaHariIni' => $menitKerjaHariIni,
            'durasiKerjaHariIni' => AbsensiHarian::formatDurasiJamMenit($menitKerjaHariIni),
            'targetHarianAsn' => AbsensiHarian::formatDurasiJamMenit(AbsensiHarian::TARGET_MENIT_HARIAN_ASN),
            'sudahMasuk' => $sudahMasuk,
            'sudahPulang' => $sudahPulang,
            'selesai' => $selesai,
            'officeLat' => (float) ($pengaturan->latitude_kantor ?? -0.69752300),
            'officeLng' => (float) ($pengaturan->longitude_kantor ?? 100.98547200),
            'maxRadius' => (int) ($pengaturan->radius_meter ?? 150),
            'namaDinas' => $pengaturan->nama_dinas ?? $pengaturan->sub_nama ?? 'Dinas Lingkungan Hidup',
            'alamatKantor' => $pengaturan->alamat_kantor ?? 'Kantor Dinas',
            'isWfh' => ($user->status_kerja === 'wfh'),
            'isAdmin' => $isAdmin,
            'modeSimulasi' => $this->modeSimulasi,
            'jadwal' => $jadwal,
            'bukaMasuk' => $bukaMasuk,
            'tutupMasuk' => $tutupMasuk,
            'jamMasukResmi' => $jamMasukResmi,
            'jamPulangResmi' => $jamPulangResmi,
            'tutupPulang' => $tutupPulang,
            'toleransi' => $toleransi,
            'batasToleransi' => $batasToleransi,
            'isJumat' => $isJumat,
            'lewatBatasMasuk' => $lewatBatasMasuk,
            'belumBukaMasuk' => $belumBukaMasuk,
            'belumJamPulang' => $belumJamPulang,
            'lewatBatasPulang' => $lewatBatasPulang,
            'hariLiburHariIni' => $hariLiburHariIni,
        ]);
    }
}
