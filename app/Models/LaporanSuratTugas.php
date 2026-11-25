<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanSuratTugas extends Model
{
    use HasFactory;

    protected $table = 'laporan_surat_tugas';

    protected $fillable = [
        'surat_tugas_id',
        'user_id',
        'ringkasan_kegiatan',
        'link_dokumentasi',
        'foto_dokumentasi',
        'dilaporkan_at',
    ];

    protected function casts(): array
    {
        return [
            'foto_dokumentasi' => 'array',
            'dilaporkan_at' => 'datetime',
        ];
    }

    public function suratTugas(): BelongsTo
    {
        return $this->belongsTo(SuratTugas::class, 'surat_tugas_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
