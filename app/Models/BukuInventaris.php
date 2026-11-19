<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BukuInventaris extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'buku_inventaris';

    protected $guarded = ['id'];

    protected $casts = [
        'nomor_urut' => 'integer',
        'tahun_pembelian' => 'integer',
        'jumlah_barang' => 'integer',
        'harga' => 'decimal:2',
    ];

    public function getFormattedHargaAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->harga, 2, ',', '.');
    }

    protected static function booted(): void
    {
        static::forceDeleting(function (BukuInventaris $record): void {
            if ($record->photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($record->photo);
            }
        });
    }
}
