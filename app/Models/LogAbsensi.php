<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAbsensi extends Model
{
    protected $table = 'log_absensi';

    protected $fillable = [
        'user_id',
        'mesin_absensi_id',
        'nomor_absensi',
        'waktu_absensi',
        'sumber',
        'metode_verifikasi',
        'kode_kerja',
        'kode_log_asli',
        'data_asli',
    ];

    protected $casts = [
        'waktu_absensi' => 'datetime',
        'data_asli' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mesinAbsensi(): BelongsTo
    {
        return $this->belongsTo(MesinAbsensi::class);
    }
}
