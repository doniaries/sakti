<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPengajuanSpj extends Model
{
    use HasFactory;

    protected $table = 'detail_pengajuan_spj';

    protected $fillable = [
        'pengajuan_spj_id',
        'kode_rekening_id',
        'master_barang_id',
        'kuantitas',
        'harga_satuan',
        'subtotal',
        'kuantitas_realisasi',
        'harga_realisasi',
        'subtotal_realisasi',
    ];

    protected $casts = [
        'kuantitas' => 'integer',
        'kuantitas_realisasi' => 'integer',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'harga_realisasi' => 'decimal:2',
        'subtotal_realisasi' => 'decimal:2',
    ];

    public function pengajuanSpj(): BelongsTo
    {
        return $this->belongsTo(PengajuanSpj::class, 'pengajuan_spj_id');
    }

    public function kodeRekening(): BelongsTo
    {
        return $this->belongsTo(KodeRekening::class, 'kode_rekening_id');
    }

    public function masterBarang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'master_barang_id');
    }
}
