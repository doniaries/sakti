<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanPersediaan extends Model
{
    use HasFactory;

    protected $table = 'laporan_persediaans';

    protected $fillable = [
        'bidang_id',
        'master_barang_id',
        'bulan',
        'tahun',
        'saldo_awal',
        'jumlah_masuk',
        'nilai_masuk',
        'jumlah_keluar',
        'nilai_keluar',
        'jumlah_expired',
        'nilai_expired',
        'saldo_akhir',
        'nilai_akhir',
    ];

    protected $casts = [
        'nilai_masuk' => 'decimal:2',
        'nilai_keluar' => 'decimal:2',
        'nilai_expired' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
    ];

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function masterBarang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'master_barang_id');
    }
}
