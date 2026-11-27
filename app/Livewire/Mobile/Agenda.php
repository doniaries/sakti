<?php

namespace App\Livewire\Mobile;

use App\Models\AgendaKegiatan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
class Agenda extends Component
{
    public bool $showForm = false;
    public string $judul = '';
    public string $tanggal = '';
    public string $jam_mulai = '08:00';
    public string $jam_selesai = '';
    public string $tempat = '';
    public string $asal_undangan = '';
    public string $keterangan = '';

    public function mount(): void
    {
        $this->tanggal = Carbon::today()->toDateString();
    }

    public function openCreate(): void
    {
        $this->reset(['judul', 'jam_selesai', 'tempat', 'asal_undangan', 'keterangan']);
        $this->tanggal = Carbon::today()->toDateString();
        $this->jam_mulai = '08:00';
        $this->showForm = true;
    }

    public function simpanAgenda(): void
    {
        $validated = $this->validate([
            'judul' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'string'],
            'jam_selesai' => ['nullable', 'string'],
            'tempat' => ['required', 'string', 'max:255'],
            'asal_undangan' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ]);

        AgendaKegiatan::create([
            ...$validated,
            'jam_selesai' => $validated['jam_selesai'] ?: null,
            'created_by' => Auth::id(),
        ]);

        $this->showForm = false;
        $this->dispatch('notify', type: 'success', title: 'Agenda Ditambahkan', message: 'Agenda kegiatan berhasil disimpan.');
    }

    public function render()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $isKadisOrAdmin = $user && $user->hasAnyRole(['super_admin', 'superadmin', 'admin', 'pa', 'kadis']);

        $baseQuery = AgendaKegiatan::query()
            ->with(['disposisis' => fn ($q) => $q->where('user_id', $user?->id)])
            ->when(! $isKadisOrAdmin && $user, function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->whereHas('disposisis', fn ($d) => $d->where('user_id', $user->id))
                        ->orWhere('created_by', $user->id);
                });
            });

        $todayAgendas = (clone $baseQuery)
            ->whereDate('tanggal', $today)
            ->orderBy('jam_mulai')
            ->get();

        $upcomingAgendas = (clone $baseQuery)
            ->whereDate('tanggal', '>', $today)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->limit(15)
            ->get();

        return view('livewire.mobile.agenda', [
            'todayAgendas' => $todayAgendas,
            'upcomingAgendas' => $upcomingAgendas,
        ]);
    }
}
