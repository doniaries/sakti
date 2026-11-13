<?php

namespace App\Observers;

use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Models\PengajuanSpj;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class PengajuanSpjObserver
{
    public function created(PengajuanSpj $spj): void
    {
        if ($spj->status && $spj->status !== 'draft') {
            $this->dispatchStatusNotifications($spj);
        }
    }

    public function updated(PengajuanSpj $spj): void
    {
        if ($spj->wasChanged('status')) {
            $this->dispatchStatusNotifications($spj);
        }
    }

    protected function dispatchStatusNotifications(PengajuanSpj $spj): void
    {
        $url = PengajuanSpjResource::getUrl('index', ['tableSearch' => $spj->nomor_pengajuan]);
        $pemohon = $spj->user?->name ?? 'Pegawai';
        $nomor = $spj->nomor_pengajuan ?? ('#' . $spj->id);

        // 1. Notifikasi ke pihak verifikator / pencairan yang dituju
        $targets = $this->resolveTargetUsers($spj);
        if ($targets->isNotEmpty()) {
            Notification::make()
                ->title("Pengajuan SPJ: {$nomor}")
                ->body("Status: {$spj->status_label} • Pemohon: {$pemohon}")
                ->icon('heroicon-o-document-text')
                ->color($spj->status_color)
                ->actions([
                    Action::make('lihat')
                        ->label('Lihat SPJ')
                        ->url($url)
                        ->markAsRead(),
                ])
                ->sendToDatabase($targets);
        }

        // 2. Notifikasi progres ke pemohon (saat status berubah atau dibuat pada tahap lanjutan)
        $isProgressStatus = in_array($spj->status, [
            'menunggu_realisasi',
            'revisi_pptk',
            'revisi_ppk',
            'revisi_pa',
            'revisi_bendahara',
            'disetujui',
            'dicairkan',
            'ditolak',
        ], true);

        if ($spj->user_id && ($spj->wasChanged('status') || $isProgressStatus) && $spj->user_id !== auth()->id()) {
            $isRevisi = str_contains((string) $spj->status, 'revisi') || $spj->status === 'ditolak';

            Notification::make()
                ->title("Progres SPJ {$nomor}")
                ->body("Status diperbarui menjadi: {$spj->status_label}")
                ->icon($isRevisi ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                ->color($spj->status_color)
                ->actions([
                    Action::make('lihat')
                        ->label('Buka')
                        ->url($url)
                        ->markAsRead(),
                ])
                ->sendToDatabase($spj->user);
        }
    }

    protected function resolveTargetUsers(PengajuanSpj $spj): Collection
    {
        $roleTargets = match ($spj->status) {
            'diajukan', 'menunggu_verifikasi_pptk' => $spj->pptk_id
                ? User::where('id', $spj->pptk_id)->get()
                : User::role('pptk')->when($spj->bidang_id, fn($q) => $q->where('bidang_id', $spj->bidang_id))->get(),
            'menunggu_verifikasi_ppk' => User::role('ppk')->get(),
            'menunggu_persetujuan_pa', 'menunggu_verifikasi_pa' => User::role('pa')->get(),
            'menunggu_verifikasi_bendahara', 'menunggu_pencairan', 'disetujui' => User::role('bendahara')->get(),
            default => collect(),
        };

        if ($roleTargets->isEmpty()) {
            return collect();
        }

        $superAdmins = User::query()
            ->where('aktif', true)
            ->whereHas('roles', fn($q) => $q->where('name', 'super_admin'))
            ->get();

        return $roleTargets
            ->merge($superAdmins)
            ->unique('id')
            ->reject(fn(User $u) => $u->id === $spj->user_id);
    }
}
