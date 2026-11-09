<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSyaratBelanja extends Model
{
    use HasFactory;

    protected $table = 'master_syarat_belanja';

    protected $fillable = [
        'jenis_belanja',
        'metode_pengadaan',
        'nama_syarat',
        'keterangan',
        'is_required',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'aktif' => 'boolean',
        'urutan' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->urutan)) {
                $max = static::where('jenis_belanja', $model->jenis_belanja)
                    ->where('metode_pengadaan', $model->metode_pengadaan)
                    ->max('urutan') ?? 0;
                $model->urutan = $max + 1;
            }
        });
    }
}
