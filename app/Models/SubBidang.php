<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubBidang extends Model
{
    protected $fillable = ['bidang_id', 'nama', 'kepala_id'];

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class);
    }

    public function kepala(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kepala_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function kegiatans(): BelongsToMany
    {
        return $this->belongsToMany(MasterKegiatan::class, 'kegiatan_sub_bidang', 'sub_bidang_id', 'kegiatan_id');
    }

    public function pengajuanSpjs(): HasMany
    {
        return $this->hasMany(PengajuanSpj::class);
    }
}
