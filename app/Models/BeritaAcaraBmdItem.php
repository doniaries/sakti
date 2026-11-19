<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeritaAcaraBmdItem extends Model
{
    use HasFactory;

    protected $table = 'berita_acara_bmd_items';

    protected $guarded = ['id'];

    protected $casts = [
        'jumlah' => 'integer',
        'nilai_perolehan' => 'decimal:2',
    ];

    public function beritaAcara(): BelongsTo
    {
        return $this->belongsTo(BeritaAcaraBmd::class, 'berita_acara_bmd_id');
    }

    public function bukuInventaris(): BelongsTo
    {
        return $this->belongsTo(BukuInventaris::class, 'buku_inventaris_id');
    }
}
