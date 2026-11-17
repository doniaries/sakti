<?php

declare(strict_types=1);

namespace App\Livewire\Mobile;

use App\Models\PengaturanAplikasi;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Pengaturan Modul Sistem - SAKTI Mobile')]
class PengaturanModul extends Component
{
    /** @var array<string, bool> */
    public array $modulAktif = [];

    public function mount(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user?->hasRole('super_admin')) {
            abort(403, 'Hanya Super Admin yang dapat mengakses halaman Pengaturan Modul.');
        }

        $this->modulAktif = PengaturanAplikasi::getModulAktifMap();
    }

    public function toggleModul(string $key): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user?->hasRole('super_admin')) {
            abort(403);
        }

        if (! array_key_exists($key, PengaturanAplikasi::DEFAULT_MODULES)) {
            return;
        }

        $current = PengaturanAplikasi::getModulAktifMap();
        $current[$key] = ! ($current[$key] ?? false);

        PengaturanAplikasi::getPengaturan()->update(['modul_aktif' => $current]);
        $this->modulAktif = PengaturanAplikasi::getModulAktifMap();

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Pengaturan Modul Diperbarui',
            'message' => 'Perubahan status modul langsung diterapkan di Web Admin, Mobile Livewire & Android.',
        ]);
    }

    public function render()
    {
        return view('livewire.mobile.pengaturan-modul');
    }
}
