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
    ];

    protected $casts = [
        'tanggal' => 'date',
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
