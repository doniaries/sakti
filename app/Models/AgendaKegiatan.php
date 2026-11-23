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
}
