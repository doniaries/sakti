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

    protected static function booted(): void
    {
        static::saving(function (KodeRekening $model) {
            if ($model->kode_lengkap) {
                $parts = explode('.', $model->kode_lengkap);
                $model->akun = filled($model->akun) ? $model->akun : ($parts[0] ?? '5');
                $model->kelompok = filled($model->kelompok) ? $model->kelompok : ($parts[1] ?? '1');
                $model->jenis = filled($model->jenis) ? $model->jenis : ($parts[2] ?? '02');
                $model->objek = filled($model->objek) ? $model->objek : ($parts[3] ?? '01');
                $model->rincian_objek = filled($model->rincian_objek) ? $model->rincian_objek : ($parts[4] ?? '001');
                $model->sub_rincian_objek = filled($model->sub_rincian_objek) ? $model->sub_rincian_objek : ($parts[5] ?? '00001');
                $model->sub_sub_rincian_objek = filled($model->sub_sub_rincian_objek) ? $model->sub_sub_rincian_objek : ($parts[6] ?? '000');
            }
        });
    }

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

