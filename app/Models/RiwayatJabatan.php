<?php

namespace App\Models;

use App\Models\Concerns\DeletesOldFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class RiwayatJabatan extends Model
{
    use DeletesOldFiles;
    use HasFactory;

    protected function fileAttributes(): array
    {
        return ['file_sk_jabatan'];
    }

    protected $table = 'riwayat_jabatans';

    protected $fillable = [
        'user_id',
        'nama_jabatan',
        'jenis_jabatan',
        'unit_kerja',
        'nomor_sk',
        'tanggal_sk',
        'tmt_jabatan',
        'file_sk_jabatan',
        'is_jabatan_sekarang',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_sk' => 'date',
            'tmt_jabatan' => 'date',
            'is_jabatan_sekarang' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (RiwayatJabatan $item): void {
            if ($item->is_jabatan_sekarang && $item->user_id) {
                static::query()
                    ->where('user_id', $item->user_id)
                    ->when($item->exists, fn ($q) => $q->whereKeyNot($item->getKey()))
                    ->update(['is_jabatan_sekarang' => false]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFileSkJabatanUrlAttribute(): ?string
    {
        if (blank($this->file_sk_jabatan)) {
            return null;
        }

        return Storage::disk('public')->url($this->file_sk_jabatan);
    }
}

