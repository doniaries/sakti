<?php

declare(strict_types=1);

namespace App\Livewire\Mobile\SuratTugas;

use App\Models\SuratTugas;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Buat Surat Tugas - SAKTI Mobile')]
class Form extends Component
{
    public string $nama_kegiatan = '';
    public string $tanggal_mulai = '';
    public string $tanggal_selesai = '';
    public string $jam_acara = '';
    public string $lokasi = '';
    public string $keterangan = '';
    /** @var array<int, int|string> */
    public array $pegawai_ids = [];

    public function mount(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user && $user->canManageSuratTugas(), 403);

        $today = now()->toDateString();
        $this->tanggal_mulai = $today;
        $this->tanggal_selesai = $today;
    }

    public function save(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user && $user->canManageSuratTugas(), 403);

        $validated = $this->validate([
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'jam_acara' => ['nullable', 'string', 'max:100'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'pegawai_ids' => ['nullable', 'array'],
            'pegawai_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $suratTugas = SuratTugas::create([
            'nama_kegiatan' => $validated['nama_kegiatan'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'jam_acara' => $validated['jam_acara'] ?: null,
            'lokasi' => $validated['lokasi'] ?: null,
            'keterangan' => $validated['keterangan'] ?: null,
            'status' => 'draft',
            'sumber' => 'mobile_pwa',
            'created_by' => $user->id,
        ]);

        if (! empty($validated['pegawai_ids'])) {
            $suratTugas->pegawais()->sync($validated['pegawai_ids']);
        }

        session()->flash('success', 'Surat Tugas berhasil dibuat.');
        $this->redirect(route('mobile.dashboard'), navigate: true);
    }

    public function render(): View
    {
        $timKreatif = User::query()
            ->whereNotNull('jabatan_tim_kreatif')
            ->where('jabatan_tim_kreatif', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name', 'jabatan_tim_kreatif']);

        return view('livewire.mobile.surat-tugas.form', [
            'timKreatif' => $timKreatif,
        ]);
    }
}
