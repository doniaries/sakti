<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStatusSpj extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'riwayat_status_spj';

    protected $fillable = [
        'pengajuan_spj_id',
        'status_sebelumnya',
        'status_berikutnya',
        'user_id',
        'peran_saat_tindakan',
        'catatan',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function pengajuanSpj(): BelongsTo
    {
        return $this->belongsTo(PengajuanSpj::class, 'pengajuan_spj_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
