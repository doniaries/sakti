<?php

namespace App\Jobs;

use App\Models\AgendaDisposisi;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendAgendaDisposisiNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $agendaDisposisiId) {}

    public function handle(): void
    {
        $disposisi = AgendaDisposisi::query()
            ->with(['agendaKegiatan', 'user'])
            ->find($this->agendaDisposisiId);

        if (! $disposisi || ! $disposisi->user || ! $disposisi->agendaKegiatan) {
            return;
        }

        $agenda = $disposisi->agendaKegiatan;
        $jam = substr((string) $agenda->jam_mulai, 0, 5);
        $tanggal = $agenda->tanggal?->format('d/m/Y') ?? '';
        $instruksi = $disposisi->instruksi ? " — Arahan: {$disposisi->instruksi}" : '';

        Notification::make()
            ->title("Agenda: {$agenda->judul}")
            ->body("{$tanggal} pukul {$jam} di {$agenda->tempat}{$instruksi}")
            ->icon('heroicon-o-calendar-days')
            ->info()
            ->sendToDatabase($disposisi->user);

        $disposisi->update(['notified_at' => now()]);
    }
}
