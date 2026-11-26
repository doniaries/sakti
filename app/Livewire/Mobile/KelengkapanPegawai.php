<?php

declare(strict_types=1);

namespace App\Livewire\Mobile;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Kelengkapan Data Pegawai - SAKTI Mobile')]
class KelengkapanPegawai extends Component
{
    public function mount(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless(
            $user && $user->hasAnyRole(['super_admin', 'superadmin', 'admin', 'pa', 'pimpinan', 'kepala_dinas', 'kabid', 'atasan']),
            403
        );
    }

    public function render(): View
    {
        $pegawais = User::query()
            ->bukanSuperAdmin()
            ->where('aktif', true)
            ->withCount(['riwayatGolongans', 'riwayatJabatans', 'riwayatPendidikans', 'keluargas'])
            ->orderBy('name')
            ->get(['id', 'name', 'nip', 'nik', 'npwp', 'no_bpjs']);

        $belumLengkap = $pegawais->map(function (User $u): ?array {
            $kurang = [];
            if (blank($u->nik) || blank($u->npwp) || blank($u->no_bpjs)) {
                $kurang[] = 'Administrasi';
            }
            if ((int) $u->riwayat_golongans_count === 0) {
                $kurang[] = 'Pangkat/Gol';
            }
            if ((int) $u->riwayat_jabatans_count === 0) {
                $kurang[] = 'Jabatan';
            }
            if ((int) $u->riwayat_pendidikans_count === 0) {
                $kurang[] = 'Pendidikan';
            }
            if ((int) $u->keluargas_count === 0) {
                $kurang[] = 'Keluarga';
            }

            if (empty($kurang)) {
                return null;
            }

            return [
                'name' => $u->name,
                'nip' => $u->nip,
                'kurang' => $kurang,
            ];
        })->filter()->values();

        $waLines = [
            '*INFO KELENGKAPAN DATA KEPEGAWAIAN SAKTI*',
            'Mohon Bapak/Ibu pegawai berikut segera melengkapi data kepegawaian di aplikasi SAKTI:',
            '',
        ];
        foreach ($belumLengkap as $idx => $item) {
            $waLines[] = ($idx + 1) . ". *{$item['name']}* — Belum: " . implode(', ', $item['kurang']);
        }
        $waLines[] = '';
        $waLines[] = 'Terima kasih.';

        return view('livewire.mobile.kelengkapan-pegawai', [
            'totalPegawai' => $pegawais->count(),
            'belumLengkap' => $belumLengkap,
            'waText' => implode("\n", $waLines),
        ]);
    }
}
