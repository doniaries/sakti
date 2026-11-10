<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterSyaratBelanja extends Model
{
    use HasFactory;

    protected $table = 'master_syarat_belanja';

    protected $fillable = [
        'master_jenis_belanja_id',
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

    public function masterJenisBelanja(): BelongsTo
    {
        return $this->belongsTo(MasterJenisBelanja::class, 'master_jenis_belanja_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->aktif = true;

            if ($model->master_jenis_belanja_id && $parent = $model->masterJenisBelanja) {
                if (empty($model->jenis_belanja)) {
                    $model->jenis_belanja = $parent->kode;
                }
                if (empty($model->metode_pengadaan)) {
                    $model->metode_pengadaan = $parent->metode_pengadaan;
                }
            }

            if (empty($model->urutan)) {
                $query = static::query();
                if ($model->master_jenis_belanja_id) {
                    $query->where('master_jenis_belanja_id', $model->master_jenis_belanja_id);
                } else {
                    $query->where('jenis_belanja', $model->jenis_belanja);
                }
                $max = $query->max('urutan') ?? 0;
                $model->urutan = $max + 1;
            }
        });

        static::saving(function (self $model): void {
            if ($model->master_jenis_belanja_id && $parent = $model->masterJenisBelanja) {
                $model->jenis_belanja = $parent->kode;
                if ($parent->metode_pengadaan !== 'semua') {
                    $model->metode_pengadaan = $parent->metode_pengadaan;
                }
            }
        });
    }
}
