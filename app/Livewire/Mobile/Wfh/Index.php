<?php

namespace App\Livewire\Mobile\Wfh;

use App\Models\AbsensiHarian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Daftar Presensi WFH - Absensi LHK Mobile')]
class Index extends Component
{
    public $bulan;
    public $tahun;

    public function mount()
    {
        $this->bulan = (int) date('m');
        $this->tahun = (int) date('Y');
    }

    public function prevMonth()
    {
        $date = Carbon::createFromDate($this->tahun, $this->bulan, 1)->subMonth();
        $this->bulan = (int) $date->format('m');
        $this->tahun = (int) $date->format('Y');
    }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->tahun, $this->bulan, 1)->addMonth();
        $this->bulan = (int) $date->format('m');
        $this->tahun = (int) $date->format('Y');
    }

    public function render()
    {
        /** @var User $user */
        $user = Auth::user();

        // Ambil riwayat presensi WFH yang telah dilaksanakan
        $riwayatWfh = AbsensiHarian::where('user_id', $user->id)
            ->whereYear('tanggal', $this->tahun)
            ->whereMonth('tanggal', $this->bulan)
            ->where(function ($q) use ($user) {
                $q->where('jenis_kehadiran', 'wfh')
                  ->orWhere('keterangan', 'like', '%wfh%');
                
                // Jika pegawai bertipe kerja WFH tetap, seluruh presensinya adalah presensi WFH
                if ($user->status_kerja === 'wfh') {
                    $q->orWhereNotNull('jam_masuk');
                }
            })
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalWfhBulanIni = $riwayatWfh->count();

        return view('livewire.mobile.wfh.index', [
            'user' => $user,
            'riwayatWfh' => $riwayatWfh,
            'totalWfhBulanIni' => $totalWfhBulanIni,
            'namaBulan' => Carbon::createFromDate($this->tahun, $this->bulan, 1)->translatedFormat('F Y'),
        ]);
    }
}
