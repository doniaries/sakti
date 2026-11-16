<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Aktivitas;
use App\Models\Lhk;
use App\Models\RencanaAksi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LhkPekerjaan extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'lhk_id',
        'jam_mulai',
        'jam_selesai',
        'lama_menit',
        'rencana_aksi_id',
        'aktivitas_id',
        'hasil_pekerjaan',
        'bukti_url',
        'status_verifikasi',
    ];

    public function lhk(): BelongsTo
    {
        return $this->belongsTo(Lhk::class);
    }

    public function aktivitas(): BelongsTo
    {
        return $this->belongsTo(Aktivitas::class);
    }

    public function rencanaAksi(): BelongsTo
    {
        return $this->belongsTo(RencanaAksi::class);
    }
}
