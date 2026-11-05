<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterTujuanBbm extends Model
{
    use HasFactory;

    protected $table = 'master_tujuan_bbm';

    protected $fillable = [
        'nama_tujuan',
        'kecamatan_id',
        'nagari_id',
        'kategori_wilayah',
        'jarak_km',
        'kuota_liter',
        'jenis_kendaraan',
        'keterangan',
        'aktif',
    ];

    protected $casts = [
        'jarak_km' => 'decimal:2',
        'kuota_liter' => 'decimal:2',
        'aktif' => 'boolean',
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function nagari(): BelongsTo
    {
        return $this->belongsTo(Nagari::class, 'nagari_id');
    }

    public function pengajuanSpjs(): HasMany
    {
        return $this->hasMany(PengajuanSpj::class, 'master_tujuan_bbm_id');
    }

    public function getLabelAttribute(): string
    {
        return "{$this->nama_tujuan} ({$this->kuota_liter} Liter)";
    }
}
