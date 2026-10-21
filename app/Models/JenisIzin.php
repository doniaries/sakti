<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisIzin extends Model
{
    use HasFactory;

    protected $table = 'jenis_izins';

    protected $fillable = [
        'nama',
        'kode',
        'kategori',
        'kuota_hari',
        'butuh_lampiran',
        'aktif',
        'keterangan',
    ];

    protected $casts = [
        'kuota_hari' => 'integer',
        'butuh_lampiran' => 'boolean',
        'aktif' => 'boolean',
    ];

    public function pengajuanIzins(): HasMany
    {
        return $this->hasMany(PengajuanIzin::class);
    }
}
