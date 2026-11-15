<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SuratTugas extends Model
{
    use HasFactory;

    protected $table = 'surat_tugas';

    protected $fillable = [
        'nomor_surat',
        'nama_kegiatan',
        'dasar_surat',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'keterangan',
        'penandatangan_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function pegawais(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'surat_tugas_user')
            ->withPivot('peran_tugas')
            ->withTimestamps();
    }

    public function penandatangan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penandatangan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateNomorSurat(): string
    {
        $tahun = now()->year;
        $urutan = static::query()->whereYear('created_at', $tahun)->count() + 1;

        return sprintf('094/%03d/ST-DISPARPORA/%d', $urutan, $tahun);
    }

    public static function defaultDasarSurat(): string
    {
        return 'Keputusan Kepala Dinas Pariwisata Pemuda dan Olahraga Kabupaten Sijunjung Nomor: 188.45/28/KPTS-DISPARPORA/2026 tentang Pembentukan Tim Kreatif Publikasi dan Promosi Dinas Pariwisata Pemuda dan Olahraga Kabupaten Sijunjung Tahun 2026.';
    }
}
