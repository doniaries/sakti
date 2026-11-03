<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KodeRekening extends Model
{
    use HasFactory;

    protected $table = 'kode_rekenings';

    protected $fillable = [
        'akun',
        'kelompok',
        'jenis',
        'objek',
        'rincian_objek',
        'sub_rincian_objek',
        'sub_sub_rincian_objek',
        'kode_lengkap',
        'uraian',
    ];

    public function masterBarangs(): HasMany
    {
        return $this->hasMany(MasterBarang::class, 'kode_rekening_id');
    }

    public function detailPengajuanSpjs(): HasMany
    {
        return $this->hasMany(DetailPengajuanSpj::class, 'kode_rekening_id');
    }

    public function getLabelAttribute(): string
    {
        return "{$this->kode_lengkap} - {$this->uraian}";
    }
}
