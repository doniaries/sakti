<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterSubKegiatan extends Model
{
    use HasFactory;

    protected $table = 'master_sub_kegiatan';

    protected $fillable = [
        'kegiatan_id',
        'jf_id',
        'tahun_anggaran',
        'kode_sub_kegiatan',
        'nama_sub_kegiatan',
    ];

    protected $casts = [
        'tahun_anggaran' => 'integer',
        'jf_id' => 'integer',
    ];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(MasterKegiatan::class, 'kegiatan_id');
    }

    public function jf(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jf_id');
    }

    public function pengajuanSpjs(): HasMany
    {
        return $this->hasMany(PengajuanSpj::class, 'sub_kegiatan_id');
    }

    public function rincianAnggarans(): HasMany
    {
        return $this->hasMany(RincianAnggaranBelanja::class, 'sub_kegiatan_id');
    }

    public function getLabelAttribute(): string
    {
        return "{$this->kode_sub_kegiatan} - {$this->nama_sub_kegiatan}";
    }

    public function getTotalPaguAttribute(): float
    {
        if (array_key_exists('total_pagu', $this->attributes)) {
            return (float) ($this->attributes['total_pagu'] ?? 0);
        }

        if (array_key_exists('rincian_anggarans_sum_pagu_total', $this->attributes)) {
            return (float) ($this->attributes['rincian_anggarans_sum_pagu_total'] ?? 0);
        }

        return (float) $this->rincianAnggarans()->sum('pagu_total');
    }

    public function getTotalRealisasiAttribute(): float
    {
        if (array_key_exists('total_realisasi', $this->attributes)) {
            return (float) ($this->attributes['total_realisasi'] ?? 0);
        }

        if (array_key_exists('rincian_anggarans_sum_nilai_realisasi', $this->attributes)) {
            return (float) ($this->attributes['rincian_anggarans_sum_nilai_realisasi'] ?? 0);
        }

        return (float) $this->rincianAnggarans()->sum('nilai_realisasi');
    }

    public function getSisaPaguAttribute(): float
    {
        return max(0, $this->total_pagu - $this->total_realisasi);
    }
}

