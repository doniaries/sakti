<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterSubKegiatan extends Model
{
    use HasFactory;

    protected $table = 'master_sub_kegiatan';

    protected $fillable = [
        'kegiatan_id',
        'kode_sub_kegiatan',
        'nama_sub_kegiatan',
    ];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(MasterKegiatan::class, 'kegiatan_id');
    }

    public function pengajuanSpjs(): HasMany
    {
        return $this->hasMany(PengajuanSpj::class, 'sub_kegiatan_id');
    }

    public function getLabelAttribute(): string
    {
        return "{$this->kode_sub_kegiatan} - {$this->nama_sub_kegiatan}";
    }
}

