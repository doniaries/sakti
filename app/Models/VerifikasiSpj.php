<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerifikasiSpj extends Model
{
    use HasFactory;

    protected $table = 'verifikasi_spj';

    protected $fillable = [
        'pengajuan_spj_id',
        'tahap',
        'user_id',
        'checklist_data',
        'catatan',
        'is_approved',
    ];

    protected $casts = [
        'checklist_data' => 'array',
        'is_approved' => 'boolean',
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
