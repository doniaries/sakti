<?php

namespace App\Livewire\Mobile\Lhk;

use App\Models\AbsensiHarian;
use App\Models\Aktivitas;
use App\Models\Lhk;
use App\Models\LhkPekerjaan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Buat LHK - Absensi LHK Mobile')]
class Form extends Component
{
    public $tanggal;
    public $absenHariIni;
    
    public $pekerjaans = [];
    public $aktivitasList = [];

    protected $rules = [
        'tanggal' => 'required|date',
        'pekerjaans.*.jam_mulai' => 'required',
        'pekerjaans.*.jam_selesai' => 'required',
        'pekerjaans.*.rencana_aksi_id' => 'required',
        'pekerjaans.*.hasil_pekerjaan' => 'required',
    ];

    public function mount()
    {
        $this->tanggal = Carbon::today()->format('Y-m-d');
        $this->aktivitasList = Aktivitas::all();
        $this->loadAbsen();
    }

    public function updatedTanggal()
    {
        $this->loadAbsen();
    }

    public function loadAbsen()
    {
        $this->absenHariIni = AbsensiHarian::where('user_id', Auth::id())
            ->where('tanggal', $this->tanggal)
            ->first();
    }

    public function addPekerjaan()
    {
        $this->pekerjaans[] = [
            'jam_mulai' => '',
            'jam_selesai' => '',
            'rencana_aksi_id' => 1,
            'aktivitas_id' => null,
            'hasil_pekerjaan' => '',
            'bukti_url' => '',
        ];
    }

    public function removePekerjaan($index)
    {
        unset($this->pekerjaans[$index]);
        $this->pekerjaans = array_values($this->pekerjaans);
    }

    public function save()
    {
        $this->validate();

        if (!$this->absenHariIni || !$this->absenHariIni->log_masuk_id || !$this->absenHariIni->log_pulang_id) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Absensi Anda belum lengkap untuk tanggal ini.']);
            return;
        }

        if (empty($this->pekerjaans)) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Minimal 1 pekerjaan harus diisi.']);
            return;
        }

        $lhk = Lhk::updateOrCreate(
            ['user_id' => Auth::id(), 'tanggal' => $this->tanggal],
            [
                'log_absensi_masuk_id' => $this->absenHariIni->log_masuk_id,
                'log_absensi_pulang_id' => $this->absenHariIni->log_pulang_id,
                'status_kehadiran' => 'Hadir'
            ]
        );

        LhkPekerjaan::where('lhk_id', $lhk->id)->delete();

        foreach ($this->pekerjaans as $p) {
            LhkPekerjaan::create([
                'lhk_id' => $lhk->id,
                'jam_mulai' => $p['jam_mulai'],
                'jam_selesai' => $p['jam_selesai'],
                'rencana_aksi_id' => $p['rencana_aksi_id'],
                'aktivitas_id' => $p['aktivitas_id'] ?: null,
                'hasil_pekerjaan' => $p['hasil_pekerjaan'],
                'bukti_url' => $p['bukti_url'] ?? null,
            ]);
        }

        $this->dispatch('notify', ['type' => 'success', 'message' => 'LHK Berhasil disimpan!']);
        return redirect()->route('mobile.lhk.index');
    }

    public function render()
    {
        return view('livewire.mobile.lhk.form');
    }
}
