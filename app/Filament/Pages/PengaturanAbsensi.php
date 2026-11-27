<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\JadwalKerja;
use App\Models\PengaturanAplikasi;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class PengaturanAbsensi extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Absensi';

    protected static ?string $navigationLabel = 'Pengaturan Absen';

    protected static ?string $title = 'Pengaturan Absen';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.pengaturan-absensi';

    public float $latitude_kantor = -0.669817;

    public float $longitude_kantor = 100.927543;

    public int $radius_meter = 25;

    public bool $mode_simulasi = false;
    public ?string $simulasi_jam_aktif = null;

    public string $alamat_kantor = '';

    public string $buka_absen_masuk = '06:00';
    public string $jam_masuk_senin_kamis = '07:30';
    public string $jam_masuk_jumat = '07:30';
    public string $tutup_absen_masuk = '09:00';
    public int $toleransi_terlambat_menit = 15;
    public string $jam_pulang_senin_kamis = '16:00';
    public string $jam_pulang_jumat = '16:30';
    public string $tutup_absen_pulang = '23:59';

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user !== null && $user->hasAnyRole([
            'super_admin',
            'admin',
        ]);
    }

    public function mount(): void
    {
        $pengaturan = PengaturanAplikasi::getPengaturan();

        $this->latitude_kantor = (float) ($pengaturan->latitude_kantor ?? -0.669817);
        $this->longitude_kantor = (float) ($pengaturan->longitude_kantor ?? 100.927543);
        $this->radius_meter = (int) ($pengaturan->radius_meter ?? 25);
        $this->mode_simulasi = (bool) ($pengaturan->mode_simulasi ?? false);
        $this->simulasi_jam_aktif = $pengaturan->simulasi_jam_aktif ?: null;
        $this->alamat_kantor = (string) ($pengaturan->alamat_kantor ?? 'Gedung Bersama Lt. III, Jl. Prof. M. Yamin, SH No. 1, Muaro Sijunjung');

        $jadwal = JadwalKerja::getDefault();
        if ($jadwal) {
            $this->buka_absen_masuk = substr((string) $jadwal->buka_absen_masuk, 0, 5) ?: '06:00';
            $this->jam_masuk_senin_kamis = substr((string) $jadwal->jam_masuk_senin_kamis, 0, 5) ?: '07:30';
            $this->jam_masuk_jumat = substr((string) $jadwal->jam_masuk_jumat, 0, 5) ?: '07:30';
            $this->tutup_absen_masuk = substr((string) $jadwal->tutup_absen_masuk, 0, 5) ?: '09:00';
            $this->toleransi_terlambat_menit = (int) ($jadwal->toleransi_terlambat_menit ?? 15);
            $this->jam_pulang_senin_kamis = substr((string) $jadwal->jam_pulang_senin_kamis, 0, 5) ?: '16:00';
            $this->jam_pulang_jumat = substr((string) $jadwal->jam_pulang_jumat, 0, 5) ?: '16:30';
            $this->tutup_absen_pulang = substr((string) $jadwal->tutup_absen_pulang, 0, 5) ?: '23:59';
        }
    }

    public function simpan(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        $this->validate([
            'latitude_kantor' => 'required|numeric|between:-90,90',
            'longitude_kantor' => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|integer|min:10|max:5000',
            'alamat_kantor' => 'nullable|string|max:500',
            'buka_absen_masuk' => 'required|date_format:H:i',
            'jam_masuk_senin_kamis' => 'required|date_format:H:i',
            'jam_masuk_jumat' => 'required|date_format:H:i',
            'tutup_absen_masuk' => 'required|date_format:H:i',
            'toleransi_terlambat_menit' => 'required|integer|min:0|max:180',
            'jam_pulang_senin_kamis' => 'required|date_format:H:i',
            'jam_pulang_jumat' => 'required|date_format:H:i',
            'tutup_absen_pulang' => 'required|date_format:H:i',
        ]);

        $pengaturan = PengaturanAplikasi::first();
        if (! $pengaturan) {
            $pengaturan = PengaturanAplikasi::getPengaturan();
        }

        $pengaturan->update([
            'latitude_kantor' => $this->latitude_kantor,
            'longitude_kantor' => $this->longitude_kantor,
            'radius_meter' => $this->radius_meter,
            'mode_simulasi' => $this->mode_simulasi,
            'simulasi_jam_aktif' => $this->mode_simulasi ? ($this->simulasi_jam_aktif ?: null) : null,
            'alamat_kantor' => $this->alamat_kantor,
        ]);

        $jadwal = JadwalKerja::getDefault();
        if ($jadwal) {
            $jadwal->update([
                'buka_absen_masuk' => $this->buka_absen_masuk . ':00',
                'jam_masuk_senin_kamis' => $this->jam_masuk_senin_kamis . ':00',
                'jam_masuk_jumat' => $this->jam_masuk_jumat . ':00',
                'tutup_absen_masuk' => $this->tutup_absen_masuk . ':00',
                'toleransi_terlambat_menit' => $this->toleransi_terlambat_menit,
                'jam_pulang_senin_kamis' => $this->jam_pulang_senin_kamis . ':00',
                'jam_pulang_jumat' => $this->jam_pulang_jumat . ':00',
                'tutup_absen_pulang' => $this->tutup_absen_pulang . ':00',
            ]);
        }

        session(['mode_simulasi_absensi' => $this->mode_simulasi]);

        Notification::make()
            ->title('Pengaturan Absen Berhasil Disimpan!')
            ->body("Titik pusat kantor ({$this->latitude_kantor}, {$this->longitude_kantor}) dengan radius {$this->radius_meter} meter telah aktif di seluruh sistem dan aplikasi mobile presensi.")
            ->success()
            ->send();

        $this->redirect(route('mobile.absensi'), navigate: true);
    }

    public function kembalikanKeTitikAsli(): void
    {
        $this->latitude_kantor = -0.669817;
        $this->longitude_kantor = 100.927543;
        $this->radius_meter = 25;

        $pengaturan = PengaturanAplikasi::first();
        if ($pengaturan) {
            $pengaturan->update([
                'latitude_kantor' => $this->latitude_kantor,
                'longitude_kantor' => $this->longitude_kantor,
                'radius_meter' => $this->radius_meter,
            ]);
        }

        $this->dispatch('lokasi-diperbarui', [
            'lat' => $this->latitude_kantor,
            'lng' => $this->longitude_kantor,
            'radius' => $this->radius_meter,
        ]);

        Notification::make()
            ->title('Titik Kantor Dikembalikan ke Asli')
            ->body("Koordinat telah diset ke titik resmi: {$this->latitude_kantor}, {$this->longitude_kantor} (Radius 25m).")
            ->info()
            ->send();
    }

    public function setKoordinat(float $lat, float $lng): void
    {
        $this->latitude_kantor = round($lat, 6);
        $this->longitude_kantor = round($lng, 6);

        $pengaturan = PengaturanAplikasi::first();
        if ($pengaturan) {
            $pengaturan->update([
                'latitude_kantor' => $this->latitude_kantor,
                'longitude_kantor' => $this->longitude_kantor,
            ]);
        }

        Notification::make()
            ->title('Koordinat Diambil!')
            ->body("Titik koordinat berhasil diset ke: {$this->latitude_kantor}, {$this->longitude_kantor} dan tersimpan otomatis ke pengaturan.")
            ->success()
            ->send();
    }
}
