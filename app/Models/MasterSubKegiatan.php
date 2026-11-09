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
        return (float) ($this->total_pagu ?? $this->rincian_anggarans_sum_pagu_total ?? $this->rincianAnggarans()->sum('pagu_total'));
    }

    public function getTotalRealisasiAttribute(): float
    {
        return (float) ($this->total_realisasi ?? $this->rincian_anggarans_sum_nilai_realisasi ?? $this->rincianAnggarans()->sum('nilai_realisasi'));
    }

    public function getSisaPaguAttribute(): float
    {
        return max(0, $this->total_pagu - $this->total_realisasi);
    }
}

