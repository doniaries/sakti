<?php

namespace App\Livewire\Mobile;

use App\Models\Instansi;
use App\Models\PengaturanAplikasi;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Pengaturan Lokasi & Peta Geofence - SAKTI')]
class PengaturanLokasi extends Component
{
    public float $latitude_kantor = -0.669694;
    public float $longitude_kantor = 100.927419;
    public int $radius_meter = 50;
    public string $alamat_kantor = '';
    public string $nama_dinas = '';
    public bool $mode_simulasi = false;

    public function mount()
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Keamanan: Hanya Super Admin dan Admin Dinas yang diizinkan
        if (! $user || ! $user->hasAnyRole(['super_admin', 'admin'])) {
            session()->flash('notify', [
                'type' => 'error',
                'title' => 'Akses Terbatas',
                'message' => 'Halaman Pengaturan Lokasi & Peta hanya dapat diakses oleh Administrator Dinas / Superadmin.',
            ]);

            return redirect()->route('mobile.absensi');
        }

        $pengaturan = PengaturanAplikasi::getPengaturan();

        $this->latitude_kantor = (float) ($pengaturan->latitude_kantor ?? -0.669694);
        $this->longitude_kantor = (float) ($pengaturan->longitude_kantor ?? 100.927419);
        $this->radius_meter = (int) ($pengaturan->radius_meter ?? 50);
        $this->alamat_kantor = (string) ($pengaturan->alamat_kantor ?? 'Kantor Dinas');
        $this->nama_dinas = (string) ($pengaturan->nama_dinas ?? $pengaturan->sub_nama ?? 'Dinas Lingkungan Hidup');
        $this->mode_simulasi = (bool) session('mode_simulasi_absensi', false);
    }

    public function simpanLokasi()
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole(['super_admin', 'admin'])) {
            abort(403);
        }

        $this->validate([
            'latitude_kantor' => 'required|numeric|between:-90,90',
            'longitude_kantor' => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|integer|min:10|max:5000',
            'alamat_kantor' => 'nullable|string|max:500',
        ]);

        $pengaturan = PengaturanAplikasi::first();
        if (! $pengaturan) {
            $pengaturan = new PengaturanAplikasi();
        }

        $pengaturan->latitude_kantor = $this->latitude_kantor;
        $pengaturan->longitude_kantor = $this->longitude_kantor;
        $pengaturan->radius_meter = $this->radius_meter;
        if (! empty($this->alamat_kantor)) {
            $pengaturan->alamat_kantor = $this->alamat_kantor;
        }
        $pengaturan->save();

        // Simpan preferensi mode simulasi admin ke sesi
        session(['mode_simulasi_absensi' => $this->mode_simulasi]);

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Lokasi Berhasil Disimpan!',
            'message' => "Titik kantor ({$this->latitude_kantor}, {$this->longitude_kantor}) dengan radius {$this->radius_meter} meter telah aktif secara terpadu.",
        ]);
    }

    public function setRadius(int $radius)
    {
        $this->radius_meter = $radius;
        $this->dispatch('radius-updated', radius: $radius);
    }

    public function toggleSimulasi()
    {
        $this->mode_simulasi = ! $this->mode_simulasi;
        session(['mode_simulasi_absensi' => $this->mode_simulasi]);

        $this->dispatch('notify', [
            'type' => 'info',
            'title' => $this->mode_simulasi ? 'Mode Simulasi Aktif' : 'Mode Simulasi Nonaktif',
            'message' => $this->mode_simulasi
                ? 'Presensi dapat dilakukan bebas lokasi untuk pengujian admin.'
                : 'Pemeriksaan radius geofencing aktif kembali normal.',
        ]);
    }

    public function render()
    {
        return view('livewire.mobile.pengaturan-lokasi');
    }
}

