<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nagari extends Model
{
    use HasFactory;

    protected $table = 'nagaris';

    protected $fillable = [
        'id',
        'kecamatan_id',
        'nama_nagari',
        'jarak_km',
        'kuota_liter_bbm',
    ];

    protected $casts = [
        'jarak_km' => 'decimal:2',
        'kuota_liter_bbm' => 'decimal:2',
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function masterTujuanBbms(): HasMany
    {
        return $this->hasMany(MasterTujuanBbm::class, 'nagari_id');
    }

    public function getLabelAttribute(): string
    {
        $kec = $this->kecamatan?->nama_kecamatan ? " (Kec. {$this->kecamatan->nama_kecamatan})" : '';
        return "{$this->nama_nagari}{$kec}";
    }
}
