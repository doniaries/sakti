<?php

namespace App\Models;

use App\Enums\GolonganRuang;
use App\Models\Concerns\DeletesOldFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class RiwayatGolongan extends Model
{
    use DeletesOldFiles;
    use HasFactory;

    protected function fileAttributes(): array
    {
        return ['file_sk_golongan'];
    }

    protected $table = 'riwayat_golongans';

    protected $fillable = [
        'user_id',
        'jenis_sk',
        'golongan',
        'nama_pangkat',
        'nomor_sk',
        'tanggal_sk',
        'tmt_golongan',
        'file_sk_golongan',
        'masa_kerja_tahun',
        'masa_kerja_bulan',
        'is_golongan_terakhir',
    ];

    protected function casts(): array
    {
        return [
            'golongan' => GolonganRuang::class,
            'tanggal_sk' => 'date',
            'tmt_golongan' => 'date',
            'is_golongan_terakhir' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (RiwayatGolongan $item) {
            // Otomatis isi nama pangkat jika belum terisi
            if (blank($item->nama_pangkat) && $item->golongan) {
                $item->nama_pangkat = static::getPangkatByGolongan($item->golongan);
            }

            if ($item->is_golongan_terakhir && $item->user_id) {
                static::query()
                    ->where('user_id', $item->user_id)
                    ->when($item->exists, fn ($q) => $q->whereKeyNot($item->getKey()))
                    ->update(['is_golongan_terakhir' => false]);
            }
        });

        static::saved(fn (RiwayatGolongan $item) => $item->syncToUser());
        static::deleted(fn (RiwayatGolongan $item) => $item->syncToUser());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFileSkGolonganUrlAttribute(): ?string
    {
        if (blank($this->file_sk_golongan)) {
            return null;
        }

        return Storage::disk('public')->url($this->file_sk_golongan);
    }

    public static function getPangkatByGolongan(GolonganRuang | string $gol): string
    {
        $val = $gol instanceof GolonganRuang ? $gol->value : (string) $gol;

        return match ($val) {
            'IV/e' => 'Pembina Utama',
            'IV/d' => 'Pembina Utama Madya',
            'IV/c' => 'Pembina Utama Muda',
            'IV/b' => 'Pembina Tingkat I',
            'IV/a' => 'Pembina',
            'III/d' => 'Penata Tingkat I',
            'III/c' => 'Penata',
            'III/b' => 'Penata Muda Tingkat I',
            'III/a' => 'Penata Muda',
            'II/d' => 'Pengatur Tingkat I',
            'II/c' => 'Pengatur',
            'II/b' => 'Pengatur Muda Tingkat I',
            'II/a' => 'Pengatur Muda',
            'I/d' => 'Juru Tingkat I',
            'I/c' => 'Juru',
            'I/b' => 'Juru Muda Tingkat I',
            'I/a' => 'Juru Muda',
            default => 'PPPK / Non-ASN',
        };
    }

    public function syncToUser(): void
    {
        $user = $this->user;
        if (! $user) {
            return;
        }

        $latest = $user->riwayatGolongan()
            ->orderByDesc('is_golongan_terakhir')
            ->orderByDesc('tmt_golongan')
            ->first();

        if ($latest) {
            $user->updateQuietly([
                'pangkat_golongan' => $latest->golongan,
                'tmt_pangkat' => $latest->tmt_golongan,
            ]);
        }
    }
}

