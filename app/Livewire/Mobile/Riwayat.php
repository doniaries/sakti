<?php

namespace App\Livewire\Mobile;

use App\Models\AbsensiHarian;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Riwayat Absensi - Absensi LHK Mobile')]
class Riwayat extends Component
{
    public $bulan;
    public $tahun;

    public function mount()
    {
        $this->bulan = date('m');
        $this->tahun = date('Y');
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

    public function render()
    {
        $riwayat = AbsensiHarian::where('user_id', Auth::id())
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('livewire.mobile.riwayat', [
            'riwayat' => $riwayat,
            'bulanNama' => Carbon::createFromDate($this->tahun, $this->bulan, 1)->translatedFormat('F Y'),
        ]);
    }
}
