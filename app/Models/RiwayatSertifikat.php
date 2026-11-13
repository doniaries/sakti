<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\DeletesOldFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class RiwayatSertifikat extends Model
{
    use DeletesOldFiles;
    use HasFactory;

    protected $table = 'riwayat_sertifikats';

    protected $fillable = [
        'user_id',
        'nama_sertifikat',
        'jenis',
        'penyelenggara',
        'nomor_sertifikat',
        'tanggal_terbit',
        'file_sertifikat',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_terbit' => 'date',
        ];
    }

    protected function fileAttributes(): array
    {
        return ['file_sertifikat'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFileSertifikatUrlAttribute(): ?string
    {
        if (blank($this->file_sertifikat)) {
            return null;
        }

        return Storage::disk('public')->url($this->file_sertifikat);
    }
}

