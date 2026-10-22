<?php

namespace App\Livewire\Mobile\Izin;

use App\Models\PengajuanIzin;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Pengajuan Izin & Cuti - Absensi LHK Mobile')]
class Index extends Component
{
    public $statusFilter = 'semua'; // 'semua', 'menunggu', 'disetujui', 'ditolak'

    public function setFilter($status)
    {
        $this->statusFilter = $status;
    }

    public function render()
    {
        $query = PengajuanIzin::with(['jenisIzin', 'user'])
            ->where('user_id', Auth::id())
            ->latest('tanggal_mulai');

        if ($this->statusFilter !== 'semua') {
            $query->where('status', $this->statusFilter);
        }

        $pengajuans = $query->get();

        return view('livewire.mobile.izin.index', [
            'pengajuans' => $pengajuans,
        ]);
    }
}
