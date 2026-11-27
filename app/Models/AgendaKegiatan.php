<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
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

        foreach ($ids->diff($existingIds) as $newUserId) {
            $this->disposisis()->create([
                'user_id' => $newUserId,
                'instruksi' => $cleanInstruksi,
            ]);
        }
    }
}
