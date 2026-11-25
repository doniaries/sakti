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
#[Title('Surat Tugas - SAKTI Mobile')]
class Index extends Component
{
    public ?int $selectedId = null;

    public function mount(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user && ($user->canManageSuratTugas() || filled($user->jabatan_tim_kreatif)), 403);
    }

    public function showDetail(int $id): void
    {
        $this->selectedId = $id;
    }

    public function closeDetail(): void
    {
        $this->selectedId = null;
    }

    public function render(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $query = SuratTugas::query()
            ->with(['pegawais:id,name'])
            ->latest('tanggal_mulai');

        if (! $user->canManageSuratTugas()) {
            $query->where('status', 'terbit')
                ->where(function ($q) use ($user): void {
                    $q->whereHas('pegawais', fn ($p) => $p->where('users.id', $user->id))
                        ->orWhereJsonContains('penanggung_jawab_ids', $user->id)
                        ->orWhereJsonContains('koordinator_acara_ids', $user->id)
                        ->orWhere('penanggung_jawab_id', $user->id);
                });
        }

        $suratTugasList = $query->limit(30)->get();
        $selectedSurat = $this->selectedId
            ? $suratTugasList->firstWhere('id', $this->selectedId)
            : null;

        return view('livewire.mobile.surat-tugas.index', [
            'suratTugasList' => $suratTugasList,
            'selectedSurat' => $selectedSurat,
            'canCreate' => $user->canManageSuratTugas(),
        ]);
    }
}
