<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RencanaAksi extends Model
{
    protected $fillable = [
        'jabatan_id',
        'bidang_id',
        'level',
        'tahun',
        'sasaran_opd',
        'sasaran_program',
        'sasaran_kegiatan',
        'sasaran_sub_kegiatan',
        'sasaran_teknis',
        'indikator',
        'rencana_aksi',
        'jenis',
        'satuan',
        'target_jan',
        'target_feb',
        'target_mar',
        'target_apr',
        'target_mei',
        'target_jun',
        'target_jul',
        'target_agu',
        'target_sep',
        'target_okt',
        'target_nov',
        'target_des',
    ];

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class);
    }
}
