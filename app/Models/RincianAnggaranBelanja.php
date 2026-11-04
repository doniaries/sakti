<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RincianAnggaranBelanja extends Model
{
    use HasFactory;

    protected $table = 'rincian_anggaran_belanja';

    protected $fillable = [
        'tahun_anggaran',
        'bidang_id',
        'sub_kegiatan_id',
        'kode_rekening_id',
        'master_barang_id',
        'nama_item',
        'spesifikasi',
        'sumber_dana',
        'volume',
        'satuan',
        'harga_satuan',
        'pagu_total',
        'volume_realisasi',
        'nilai_realisasi',
        'keterangan',
    ];

    protected $casts = [
        'tahun_anggaran' => 'integer',
        'volume' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'pagu_total' => 'decimal:2',
        'volume_realisasi' => 'decimal:2',
        'nilai_realisasi' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (RincianAnggaranBelanja $model) {
            if ($model->volume !== null && $model->harga_satuan !== null) {
                $model->pagu_total = (float) $model->volume * (float) $model->harga_satuan;
            }
        });
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(MasterSubKegiatan::class, 'sub_kegiatan_id');
    }

    public function kodeRekening(): BelongsTo
    {
        return $this->belongsTo(KodeRekening::class, 'kode_rekening_id');
    }

    public function masterBarang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'master_barang_id');
    }

    public function spjDetails(): HasMany
    {
        return $this->hasMany(DetailPengajuanSpj::class, 'rincian_anggaran_belanja_id');
    }

    public function getSisaVolumeAttribute(): float
    {
        return max(0, (float) $this->volume - (float) $this->volume_realisasi);
    }

    public function getSisaAnggaranAttribute(): float
    {
        return max(0, (float) $this->pagu_total - (float) $this->nilai_realisasi);
    }

    public function getPersenRealisasiAttribute(): float
    {
        return (float) $this->pagu_total > 0
            ? min(100, round(((float) $this->nilai_realisasi / (float) $this->pagu_total) * 100, 1))
            : 0;
    }

    protected function label(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->nama_item} (Pagu: " . number_format((float) $this->volume, 0, ',', '.') . " {$this->satuan} - Sisa: " . number_format($this->sisa_volume, 0, ',', '.') . " {$this->satuan})"
        );
    }
}

