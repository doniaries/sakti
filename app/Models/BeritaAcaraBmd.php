<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BeritaAcaraBmd extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'berita_acara_bmds';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(BeritaAcaraBmdItem::class, 'berita_acara_bmd_id');
    }

    public function pihakPertama(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pihak_pertama_id');
    }

    public function pihakKedua(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pihak_kedua_id');
    }
}
