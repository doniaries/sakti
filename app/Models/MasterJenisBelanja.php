<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MasterJenisBelanja extends Model
{
    use HasFactory;

    protected $table = 'master_jenis_belanja';

    protected $fillable = [
        'kode',
        'nama',
        'metode_pengadaan',
        'deskripsi',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'urutan' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->kode)) {
                $base = Str::slug($model->nama, '_');
                $kode = $base ?: 'jenis_' . time();
                $counter = 1;
                while (static::where('kode', $kode)->exists()) {
                    $counter++;
                    $kode = "{$base}_{$counter}";
                }
                $model->kode = $kode;
            }
            if (empty($model->urutan)) {
                $max = static::max('urutan') ?? 0;
                $model->urutan = $max + 1;
            }
        });

        static::updated(function (self $model): void {
            if ($model->wasChanged(['kode', 'metode_pengadaan'])) {
                $updates = ['jenis_belanja' => $model->kode];
                if ($model->metode_pengadaan !== 'semua') {
                    $updates['metode_pengadaan'] = $model->metode_pengadaan;
                }
                $model->syaratBelanjas()->update($updates);
            }
        });
    }

    public function syaratBelanjas(): HasMany
    {
        return $this->hasMany(MasterSyaratBelanja::class, 'master_jenis_belanja_id')->orderBy('urutan');
    }
}

