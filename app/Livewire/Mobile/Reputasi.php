<?php

namespace App\Livewire\Mobile;

use App\Models\Reputasi as ReputasiModel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Penilaian Rekan Kerja - Absensi LHK Mobile')]
class Reputasi extends Component
{
    public $activeTab = 'berikan'; // 'berikan', 'diterima', 'diberikan'
    public $user_id = '';
    public $nilai_reputasi = 5;
    public $keterangan = '';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function setBintang($star)
    {
        $this->nilai_reputasi = $star;
    }

    public function simpanPenilaian()
    {
        $this->validate([
            'user_id' => 'required|exists:users,id',
            'nilai_reputasi' => 'required|integer|min:1|max:5',
            'keterangan' => 'required|string|min:3|max:500',
        ], [
            'user_id.required' => 'Pilih rekan kerja yang ingin dinilai.',
            'keterangan.required' => 'Catatan penilaian / apresiasi wajib diisi.',
        ]);

        if ((int) $this->user_id === (int) Auth::id()) {
            $this->addError('user_id', 'Anda tidak dapat menilai diri sendiri.');
            return;
        }

        ReputasiModel::create([
            'user_id' => $this->user_id,
            'penilai_id' => Auth::id(),
            'tanggal' => now()->toDateString(),
            'nilai_reputasi' => $this->nilai_reputasi,
            'keterangan' => $this->keterangan,
        ]);

        $this->reset(['user_id', 'keterangan']);
        $this->nilai_reputasi = 5;
        $this->activeTab = 'diberikan';

        session()->flash('success', 'Penilaian rekan kerja berhasil dikirim!');
    }

    public function render()
    {
        $currentUserId = Auth::id();

        // Rekan kerja (selain diri sendiri)
        $rekanKerjas = User::query()
            ->where('id', '!=', $currentUserId)
            ->where('status_pegawai', 'aktif')
            ->orderBy('name', 'asc')
            ->get();

        // Penilaian yang diterima oleh user yang login
        $reputasiDiterima = ReputasiModel::with(['penilai', 'penilai.jabatan'])
            ->where('user_id', $currentUserId)
            ->latest()
            ->get();

        // Penilaian yang diberikan oleh user yang login
        $reputasiDiberikan = ReputasiModel::with(['user', 'user.jabatan'])
            ->where('penilai_id', $currentUserId)
            ->latest()
            ->get();

        // Rata-rata bintang diterima
        $avgBintang = $reputasiDiterima->avg('nilai_reputasi') ?? 5.0;

        return view('livewire.mobile.reputasi', [
            'rekanKerjas' => $rekanKerjas,
            'reputasiDiterima' => $reputasiDiterima,
            'reputasiDiberikan' => $reputasiDiberikan,
            'avgBintang' => number_format((float) $avgBintang, 1),
        ]);
    }
}
