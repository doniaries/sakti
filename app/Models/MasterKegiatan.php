<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterKegiatan extends Model
{
    use HasFactory;

    protected $table = 'master_kegiatan';

    protected $fillable = [
        'bidang_id',
        'kode_kegiatan',
        'nama_kegiatan',
        'pagu_anggaran',
    ];

    protected $casts = [
        'pagu_anggaran' => 'decimal:2',
    ];

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function subKegiatans(): HasMany
    {
        return $this->hasMany(MasterSubKegiatan::class, 'kegiatan_id');
    }

    public function pengajuanSpjs(): HasMany
    {
        return $this->hasMany(PengajuanSpj::class, 'kegiatan_id');
    }

    public function getLabelAttribute(): string
    {
        return "{$this->kode_kegiatan} - {$this->nama_kegiatan}";
    }
}
