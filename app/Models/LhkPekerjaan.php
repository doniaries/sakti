<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LhkPekerjaan extends Model
{
    protected $fillable = [
        'lhk_id',
        'jam_mulai',
        'jam_selesai',
        'lama_menit',
        'rencana_aksi',
        'kamus_aktivitas',
        'hasil_pekerjaan',
        'bukti_url',
    ];

    public function lhk(): BelongsTo
    {
        return $this->belongsTo(Lhk::class);
    }
}
