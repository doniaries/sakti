<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Notifications\Notification;
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
        static::deleting(function (BukuInventaris $aset): ?bool {
            $usedInBa = BeritaAcaraBmdItem::query()
                ->where('buku_inventaris_id', $aset->id)
                ->exists();

            if ($usedInBa) {
                Notification::make()
                    ->danger()
                    ->title('Aset Tidak Dapat Dihapus')
                    ->body('Barang inventaris ini sudah tercatat dalam Berita Acara BMD.')
                    ->send();

                return false;
            }

            return null;
        });

        static::deleting(function (BukuInventaris $aset): ?bool {
            $usedInBa = BeritaAcaraBmdItem::query()
                ->where('buku_inventaris_id', $aset->id)
                ->exists();

            if ($usedInBa) {
                Notification::make()
                    ->danger()
                    ->title('Aset Tidak Dapat Dihapus')
                    ->body('Barang inventaris ini sudah tercatat dalam Berita Acara BMD.')
                    ->send();

                return false;
            }

            return null;
        });

        static::forceDeleting(function (BukuInventaris $record): void {
            if ($record->photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($record->photo);
            }
        });
    }
}
