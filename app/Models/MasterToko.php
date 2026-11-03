<?php

namespace App\Models;

use App\Enums\NamaBank;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterToko extends Model
{
    use HasFactory;

    protected $table = 'master_tokos';

    protected $fillable = [
        'nama_toko',
        'nama_pemilik',
        'telepon',
        'alamat',
        'nama_bank',
        'nomor_rekening',
        'npwp',
        'is_active',
    ];

    protected $casts = [
        'nama_bank' => NamaBank::class,
        'is_active' => 'boolean',
    ];

    public function pengajuanSpjs(): HasMany
    {
        return $this->hasMany(PengajuanSpj::class, 'master_toko_id');
    }

    public function getLabelAttribute(): string
    {
        $pemilik = $this->nama_pemilik ? " — {$this->nama_pemilik}" : '';

        return "{$this->nama_toko}{$pemilik}";
    }
}
