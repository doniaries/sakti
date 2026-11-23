<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaDisposisi extends Model
{
    protected $fillable = [
        'agenda_kegiatan_id',
        'user_id',
        'instruksi',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'notified_at' => 'datetime',
        ];
    }

    public function agendaKegiatan(): BelongsTo
    {
        return $this->belongsTo(AgendaKegiatan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
