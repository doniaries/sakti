<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MesinAbsensi extends Model
{
    protected $table = 'mesin_absensi';

    protected $fillable = [
        'nama',
        'merek',
        'model',
        'alamat_ip',
        'port',
        'nomor_seri',
        'jenis_koneksi',
        'aktif',
        'sinkronisasi_terakhir',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'sinkronisasi_terakhir' => 'datetime',
    ];

    public function logAbsensi(): HasMany
    {
        return $this->hasMany(LogAbsensi::class);
    }
}
