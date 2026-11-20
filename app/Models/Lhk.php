<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lhk extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'log_absensi_masuk_id',
        'log_absensi_pulang_id',
        'status_kehadiran',
        'potongan_terlambat',
        'potongan_pulang_cepat',
        'potongan_kehadiran',
        'potongan_kegiatan',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logAbsensiMasuk(): BelongsTo
    {
        return $this->belongsTo(LogAbsensi::class, 'log_absensi_masuk_id');
    }

    public function logAbsensiPulang(): BelongsTo
    {
        return $this->belongsTo(LogAbsensi::class, 'log_absensi_pulang_id');
    }

    public function pekerjaans(): HasMany
    {
        return $this->hasMany(LhkPekerjaan::class);
    }
}
