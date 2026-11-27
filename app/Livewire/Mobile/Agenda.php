<?php

namespace App\Livewire\Mobile;

use App\Models\AgendaKegiatan;
use App\Models\User;
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
            'pegawais' => User::where('aktif', true)->with('jabatan:id,nama')->orderBy('id')->get(['id', 'name', 'jabatan_id']),
        ]);
    }
}
