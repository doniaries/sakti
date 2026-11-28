<?php

namespace App\Models;

use App\Services\PushNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class AgendaKegiatan extends Model
{
    protected $fillable = [
        'judul',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'tempat',
        'asal_undangan',
        'keterangan',
        'lampiran_file',
        'reminder_sent_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function disposisis(): HasMany
    {
        return $this->hasMany(AgendaDisposisi::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Sinkronisasi daftar penerima disposisi beserta instruksi seragam.
     *
     * @param  array<int, int|string>  $userIds
     */
    public function syncDisposisis(array $userIds, ?string $instruksi = null): void
    {
        $ids = collect($userIds)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        $cleanInstruksi = filled($instruksi) ? trim((string) $instruksi) : null;

        $this->disposisis()->whereNotIn('user_id', $ids->all())->delete();

        $existingIds = $this->disposisis()->pluck('user_id')->map(fn ($id): int => (int) $id);

        $existingToUpdate = $ids->intersect($existingIds);
        if ($existingToUpdate->isNotEmpty()) {
            $this->disposisis()
                ->whereIn('user_id', $existingToUpdate->all())
                ->update(['instruksi' => $cleanInstruksi]);
        }

        $newIds = $ids->diff($existingIds)->values();
        if ($newIds->isNotEmpty()) {
            $now = now();
            $rows = $newIds->map(fn (int $uid): array => [
                'agenda_kegiatan_id' => $this->id,
                'user_id' => $uid,
                'instruksi' => $cleanInstruksi,
                'notified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            AgendaDisposisi::insert($rows);
            $this->notifyDisposisiRecipients($newIds->all(), $cleanInstruksi);
        }
    }

    /**
     * Kirim notifikasi (Database, FCM, WebPush) setelah response agar UI tetap responsif.
     *
     * @param  array<int, int>  $recipientIds
     */
    public function notifyDisposisiRecipients(array $recipientIds, ?string $instruksi = null): void
    {
        if ($recipientIds === []) {
            return;
        }

        $judul = $this->judul;
        $tempat = $this->tempat;
        $waktu = Carbon::parse($this->tanggal)->translatedFormat('d M Y') . ' ' . substr((string) $this->jam_mulai, 0, 5);
        $instruksiTeks = filled($instruksi) ? trim((string) $instruksi) : 'Hadir mewakili / menindaklanjuti';
        $agendaUrl = route('mobile.agenda');

        dispatch(function () use ($recipientIds, $judul, $waktu, $tempat, $instruksiTeks, $agendaUrl): void {
            $recipients = User::whereIn('id', $recipientIds)->where('aktif', true)->get();
            if ($recipients->isEmpty()) {
                return;
            }

            PushNotificationService::sendToUsers(
                $recipients,
                'Perintah Menghadiri Agenda',
                "{$judul} ({$waktu} di {$tempat}) — {$instruksiTeks}",
                ['url' => $agendaUrl],
                'info'
            );
        })->afterResponse();
    }
}
