<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'deskripsi',
        'icon',
        'url',
        'target',
        'warna',
        'bentuk',
        'kategori',
        'badge',
        'lampiran_path',
        'urutan',
        'is_aktif',
        'akses_role',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
        'urutan' => 'integer',
    ];

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true)->orderBy('urutan', 'asc');
    }

    /**
     * Resolves the full URL (supporting external links, asset uploads, or internal routes).
     */
    public function getLinkUrlAttribute(): string
    {
        if (filled($this->lampiran_path)) {
            return asset('storage/' . $this->lampiran_path);
        }

        if (str_starts_with($this->url, 'http://') || str_starts_with($this->url, 'https://')) {
            return $this->url;
        }

        return url($this->url ?: '/');
    }
}
