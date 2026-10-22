<?php

namespace App\Livewire\Mobile;

use App\Models\HariLibur as HariLiburModel;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Kalender Libur & Cuti Bersama - Absensi LHK Mobile')]
class HariLibur extends Component
{
    public $tahun;
    public $filterJenis = 'semua'; // 'semua', 'libur_nasional', 'cuti_bersama'

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

    public function render()
    {
        $query = HariLiburModel::query()
            ->whereYear('tanggal', $this->tahun)
            ->where('is_aktif', true)
            ->orderBy('tanggal', 'asc');

        if ($this->filterJenis !== 'semua') {
            $query->where('jenis', $this->filterJenis);
        }

        $hariLiburs = $query->get();

        return view('livewire.mobile.hari-libur', [
            'hariLiburs' => $hariLiburs,
        ]);
    }
}
