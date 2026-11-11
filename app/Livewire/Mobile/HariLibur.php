<?php

namespace App\Livewire\Mobile;

use App\Models\HariLibur as HariLiburModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Kalender Libur & Cuti Bersama - Absensi LHK Mobile')]
class HariLibur extends Component
{
    public $tahun;
    public $filterJenis = 'semua'; // 'semua', 'nasional', 'cuti_bersama'

    public function mount()
    {
        $this->tahun = (int) date('Y');
    }

    public function prevYear()
    {
        $this->tahun--;
    }

    public function nextYear()
    {
        $this->tahun++;
    }

    public function setFilterJenis($jenis)
    {
        $this->filterJenis = $jenis;
    }

    /**
     * Tarik data libur resmi dari API SKB (Khusus Superadmin & Admin)
     */
    public function sinkronkanApi()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['super_admin', 'admin'])) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Akses Ditolak!',
                'message' => 'Hanya Superadmin dan Admin yang memiliki hak akses menarik data libur dari API.',
            ]);
            return;
        }

        $count = HariLiburModel::syncFromApi($this->tahun);
        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Sinkronisasi Berhasil!',
            'message' => "Berhasil memperbarui {$count} data libur & cuti bersama tahun {$this->tahun} dari API Resmi.",
        ]);
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isAdmin = $user && $user->hasAnyRole(['super_admin', 'admin']);

        // Base Query untuk filter aktif
        $baseYearQuery = HariLiburModel::query()
            ->whereYear('tanggal', $this->tahun)
            ->where('is_aktif', true);

        // Hitung Count masing-masing kategori
        $countSemua = (clone $baseYearQuery)->count();
        $countNasional = (clone $baseYearQuery)->where('jenis', 'nasional')->count();
        $countCutiBersama = (clone $baseYearQuery)->where('jenis', 'cuti_bersama')->count();

        $query = (clone $baseYearQuery)->orderBy('tanggal', 'asc');

        if ($this->filterJenis !== 'semua') {
            $dbJenis = ($this->filterJenis === 'libur_nasional') ? 'nasional' : $this->filterJenis;
            $query->where('jenis', $dbJenis);
        }

        $hariLiburs = $query->get();

        return view('livewire.mobile.hari-libur', [
            'hariLiburs' => $hariLiburs,
            'isAdmin' => $isAdmin,
            'countSemua' => $countSemua,
            'countNasional' => $countNasional,
            'countCutiBersama' => $countCutiBersama,
        ]);
    }
}
