<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterBarang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'master_barangs';

    protected $fillable = [
        'kode_rekening_id',
        'kode_barang',
        'uraian',
        'nama_barang',
        'satuan_id',
        'harga_satuan',
        'keterangan',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
    ];

    public function kodeRekening(): BelongsTo
    {
        return $this->belongsTo(KodeRekening::class, 'kode_rekening_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function stokBarangs(): HasMany
    {
        return $this->hasMany(StokBarang::class, 'master_barang_id');
    }

    public function detailPengajuanSpjs(): HasMany
    {
        return $this->hasMany(DetailPengajuanSpj::class, 'master_barang_id');
    }

    public function getLabelAttribute(): string
    {
        $satuanNama = $this->satuan?->nama ?? 'Unit';
        $hargaFormatted = number_format((float) $this->harga_satuan, 0, ',', '.');
        return "{$this->nama_barang} ({$satuanNama}) - Rp {$hargaFormatted}";
    }
}
