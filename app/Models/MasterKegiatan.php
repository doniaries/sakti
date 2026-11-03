<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterKegiatan extends Model
{
    use HasFactory;

    protected $table = 'master_kegiatan';

    protected $fillable = [
        'kode_kegiatan',
        'nama_kegiatan',
    ];

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

