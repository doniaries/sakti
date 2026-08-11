<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiHarian extends Model
{
    protected $table = 'absensi_harian';

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'log_masuk_id',
        'log_pulang_id',
        'status',
        'keterangan',
        'is_absen_mesin',
        'is_absen_mobile',
        'status_apel',
        'latitude_masuk',
        'longitude_masuk',
        'foto_masuk',
        'latitude_pulang',
        'longitude_pulang',
        'foto_pulang',
        'bukti',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_absen_mesin' => 'boolean',
        'is_absen_mobile' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logMasuk(): BelongsTo
    {
        return $this->belongsTo(LogAbsensi::class, 'log_masuk_id');
    }

    public function logPulang(): BelongsTo
    {
        return $this->belongsTo(LogAbsensi::class, 'log_pulang_id');
    }
}
