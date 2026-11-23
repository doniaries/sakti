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
    public function render()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $isKadisOrAdmin = $user && $user->hasAnyRole(['super_admin', 'admin', 'pa', 'kadis']);

        $baseQuery = AgendaKegiatan::query()
            ->with(['disposisis' => fn ($q) => $q->where('user_id', $user?->id)])
            ->when(! $isKadisOrAdmin && $user, function ($query) use ($user) {
                $query->whereHas('disposisis', fn ($q) => $q->where('user_id', $user->id));
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
