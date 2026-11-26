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
        abort_unless($user !== null, 403);

        $stId = request()->query('surat_tugas_id');
        if ($stId && ! SuratTugas::whereKey($stId)->exists()) {
            $this->dispatch('notify', type: 'warning', title: 'Surat Tugas Tidak Ditemukan', message: 'Surat tugas yang dimaksud sudah tidak ada atau telah dihapus.');
        }
    }

    public function showDetail(int $id): void
    {
        $this->selectedId = $id;
    }

    public function closeDetail(): void
    {
        $this->selectedId = null;
    }

    public function hapus(int $id): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user && $user->canManageSuratTugas(), 403);

        $surat = SuratTugas::find($id);
        if (! $surat) {
            return;
        }

        $surat->pegawais()->detach();
        $surat->delete();

        if ($this->selectedId === $id) {
            $this->selectedId = null;
        }

        $this->dispatch('notify', type: 'success', title: 'Berhasil', message: 'Surat tugas berhasil dihapus.');
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

        $myTotalTerbit = SuratTugas::query()
            ->where('status', 'terbit')
            ->where(function ($q) use ($user): void {
                $q->whereHas('pegawais', fn ($p) => $p->where('users.id', $user->id))
                    ->orWhereJsonContains('penanggung_jawab_ids', $user->id)
                    ->orWhereJsonContains('koordinator_acara_ids', $user->id)
                    ->orWhere('penanggung_jawab_id', $user->id);
            })
            ->count();

        $myBulanIni = SuratTugas::query()
            ->where('status', 'terbit')
            ->whereMonth('tanggal_mulai', now()->month)
            ->whereYear('tanggal_mulai', now()->year)
            ->where(function ($q) use ($user): void {
                $q->whereHas('pegawais', fn ($p) => $p->where('users.id', $user->id))
                    ->orWhereJsonContains('penanggung_jawab_ids', $user->id)
                    ->orWhereJsonContains('koordinator_acara_ids', $user->id)
                    ->orWhere('penanggung_jawab_id', $user->id);
            })
            ->count();

        return view('livewire.mobile.surat-tugas.index', [
            'suratTugasList' => $suratTugasList,
            'selectedSurat' => $selectedSurat,
            'canCreate' => $user->canManageSuratTugas(),
            'myTotalTerbit' => $myTotalTerbit,
            'myBulanIni' => $myBulanIni,
        ]);
    }
}
