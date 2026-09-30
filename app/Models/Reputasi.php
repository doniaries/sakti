<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reputasi extends Model
{
    protected $fillable = ['user_id', 'penilai_id', 'tanggal', 'nilai_reputasi', 'keterangan'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }
}
