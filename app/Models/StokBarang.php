<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokBarang extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'stok_barangs';

    protected $fillable = [
        'bidang_id',
        'master_barang_id',
        'stok_tersedia',
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

