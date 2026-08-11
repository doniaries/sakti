<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiMobile extends Model
{
    protected $table = 'absensi_mobile';

    protected $fillable = [
        'user_id',
        'waktu_absensi',
        'jenis',
        'latitude',
        'longitude',
        'akurasi_lokasi',
        'foto',
        'keterangan',
        'perangkat_id',
    ];

    protected $casts = [
        'waktu_absensi' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'akurasi_lokasi' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
