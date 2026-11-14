<?php

namespace App\Observers;

use App\Filament\Resources\PengajuanIzins\PengajuanIzinResource;
use App\Models\PengajuanIzin;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class PengajuanIzinObserver
{
    public function created(PengajuanIzin $izin): void
    {
        $targets = $this->resolveTargetUsers($izin);
        if ($targets->isEmpty()) {
            return;
        }

        $pemohon = $izin->user?->name ?? 'Pegawai';
        $jenis = $izin->jenisIzin?->nama ?? ucfirst((string) ($izin->jenis ?? 'Izin/Sakit'));
        $url = class_exists(PengajuanIzinResource::class)
            ? PengajuanIzinResource::getUrl('index')
            : url('/admin');

        Notification::make()
            ->title("Pengajuan {$jenis} Baru")
            ->body("{$pemohon} mengajukan {$jenis} ({$izin->tanggal_mulai?->format('d/m/Y')}).")
            ->icon('heroicon-o-clipboard-document-list')
            ->color('warning')
            ->actions([
                Action::make('lihat')
                    ->label('Tinjau Pengajuan')
                    ->url($url)
                    ->markAsRead(),
            ])
            ->sendToDatabase($targets);
    }

    public function updated(PengajuanIzin $izin): void
    {
        if (! $izin->wasChanged('status') || ! $izin->user) {
            return;
        }

        $jenis = $izin->jenisIzin?->nama ?? ucfirst((string) ($izin->jenis ?? 'Izin/Sakit'));
        $statusLabel = match ($izin->status) {
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => ucfirst((string) $izin->status),
        };
        $color = match ($izin->status) {
            'disetujui' => 'success',
            'ditolak' => 'danger',
            default => 'info',
        };
        $url = class_exists(PengajuanIzinResource::class)
            ? PengajuanIzinResource::getUrl('index')
            : url('/admin');

        Notification::make()
            ->title("Progres {$jenis}: {$statusLabel}")
            ->body("Pengajuan {$jenis} Anda tanggal {$izin->tanggal_mulai?->format('d/m/Y')} telah {$statusLabel}.")
            ->icon($izin->status === 'disetujui' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
            ->color($color)
            ->actions([
                Action::make('lihat')
                    ->label('Lihat Detail')
                    ->url($url)
                    ->markAsRead(),
            ])
            ->sendToDatabase($izin->user);
    }

    protected function resolveTargetUsers(PengajuanIzin $izin): Collection
    {
        $atasan = $izin->user?->atasan;

        if ($atasan && $atasan->aktif && $atasan->id !== $izin->user_id) {
            return collect([$atasan]);
        }

        return User::query()
            ->where('aktif', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin_kepegawaian', 'admin']))
            ->where('id', '!=', $izin->user_id)
            ->get();
    }
}
