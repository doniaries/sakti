<?php

namespace App\Livewire\Mobile;

use App\Models\AbsensiHarian;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Riwayat Absensi - Absensi LHK Mobile')]
class Riwayat extends Component
{
    public $bulan;
    public $tahun;

    #[Url(as: 'status')]
    public $filterStatus = 'semua';

    public function mount()
    {
        $this->bulan = request()->query('bulan', date('m'));
        $this->tahun = request()->query('tahun', date('Y'));
        
        if (request()->has('status')) {
            $this->filterStatus = request()->query('status');
        }
    }

    public function prevMonth()
    {
        $date = Carbon::createFromDate($this->tahun, $this->bulan, 1)->subMonth();
        $this->bulan = $date->format('m');
        $this->tahun = $date->format('Y');
    }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->tahun, $this->bulan, 1)->addMonth();
        $this->bulan = $date->format('m');
        $this->tahun = $date->format('Y');
    }

    public function setFilter($status)
    {
        $this->filterStatus = $status;
    }

    public function render()
    {
        $userId = Auth::id();

        // Ambil semua absensi di bulan bersangkutan untuk rekap card
        $allBulanIni = AbsensiHarian::where('user_id', $userId)
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->get();

        $rekap = [
            'total' => $allBulanIni->count(),
            'hadir' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['hadir', 'terlambat']))->count(),
            'terlambat' => $allBulanIni->filter(fn($a) => ($a->status?->value ?? $a->status) === 'terlambat')->count(),
            'dinas_luar' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['dinas_luar', 'dl']))->count(),
            'izin_sakit' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['izin', 'sakit']))->count(),
            'alpa' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['tidak_hadir', 'alpa']))->count(),
        ];

        // Query berdasarkan filter aktif
        $query = AbsensiHarian::where('user_id', $userId)
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->with(['logMasuk', 'logPulang']);

        if ($this->filterStatus && $this->filterStatus !== 'semua') {
            if ($this->filterStatus === 'hadir') {
                $query->whereIn('status', ['hadir', 'terlambat']);
            } elseif ($this->filterStatus === 'izin_sakit') {
                $query->whereIn('status', ['izin', 'sakit']);
            } elseif ($this->filterStatus === 'tidak_hadir') {
                $query->whereIn('status', ['tidak_hadir', 'alpa']);
            } else {
                $query->where('status', $this->filterStatus);
            }
        }

        $riwayat = $query->orderBy('tanggal', 'desc')->get();

        return view('livewire.mobile.riwayat', [
            'riwayat' => $riwayat,
            'rekap' => $rekap,
            'bulanNama' => Carbon::createFromDate($this->tahun, $this->bulan, 1)->translatedFormat('F Y'),
        ]);
    }
}
