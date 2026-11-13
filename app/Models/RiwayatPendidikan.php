<?php

namespace App\Models;

use App\Enums\JenjangPendidikan;
use App\Models\Concerns\DeletesOldFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class RiwayatPendidikan extends Model
{
    use DeletesOldFiles;
    use HasFactory;

    protected function fileAttributes(): array
    {
        return ['file_ijazah'];
    }

    protected $table = 'riwayat_pendidikans';

    protected $fillable = [
        'user_id',
        'jenjang',
        'nama_institusi',
        'jurusan',
        'tahun_lulus',
        'nomor_ijazah',
        'file_ijazah',
        'is_pendidikan_terakhir',
    ];

    protected function casts(): array
    {
        return [
            'jenjang' => JenjangPendidikan::class,
            'tahun_lulus' => 'integer',
            'is_pendidikan_terakhir' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (RiwayatPendidikan $item) {
            $item->syncToUser();
        });

        static::deleted(function (RiwayatPendidikan $item) {
            $item->syncToUser();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * URL publik untuk download atau view file ijazah PDF
     */
    public function getFileIjazahUrlAttribute(): ?string
    {
        if (blank($this->file_ijazah)) {
            return null;
        }

        return Storage::disk('public')->url($this->file_ijazah);
    }

    /**
     * Sinkronisasi data pendidikan tertinggi ke kolom profil User
     */
    public function syncToUser(): void
    {
        $user = $this->user;
        if (! $user) {
            return;
        }

        $latest = $user->riwayatPendidikan()
            ->orderByDesc('is_pendidikan_terakhir')
            ->orderByRaw("FIELD(jenjang, 'S3', 'S2', 'S1', 'D4', 'D3', 'D2', 'D1', 'SLTA', 'SLTP', 'SD') ASC")
            ->orderByDesc('tahun_lulus')
            ->first();

        if ($latest) {
            $jenjangVal = $latest->jenjang instanceof JenjangPendidikan 
                ? $latest->jenjang->value 
                : (string) $latest->jenjang;

            $namaPendidikan = trim($jenjangVal . ' ' . ($latest->jurusan ?: $latest->nama_institusi));

            $user->updateQuietly([
                'jenjang_pendidikan' => $jenjangVal,
                'pendidikan' => $namaPendidikan,
            ]);
        }
    }
}
