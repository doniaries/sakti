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
     * Resolves the primary URL.
     */
    public function getLinkUrlAttribute(): string
    {
        if (filled($this->url) && $this->url !== '#') {
            if (str_starts_with($this->url, 'http://') || str_starts_with($this->url, 'https://')) {
                return $this->url;
            }
            return url($this->url);
        }

        if (filled($this->lampiran_path)) {
            return $this->lampiran_url;
        }

        return url('/');
    }

    /**
     * Resolves the download / preview URL for attachments.
     */
    public function getLampiranUrlAttribute(): ?string
    {
        return filled($this->lampiran_path) ? asset('storage/' . $this->lampiran_path) : null;
    }

    /**
     * Maps Heroicon or generic strings to FontAwesome icons.
     */
    public function getFaIconAttribute(): string
    {
        $icon = $this->icon ?? '';

        $map = [
            'heroicon-o-banknotes' => 'fa-solid fa-money-bill-wave',
            'heroicon-o-users' => 'fa-solid fa-users',
            'heroicon-o-user-group' => 'fa-solid fa-user-group',
            'heroicon-o-device-phone-mobile' => 'fa-solid fa-mobile-screen-button',
            'heroicon-o-document-chart-bar' => 'fa-solid fa-file-waveform',
            'heroicon-o-shield-check' => 'fa-solid fa-shield-halved',
            'heroicon-o-document-arrow-down' => 'fa-solid fa-file-lines',
            'heroicon-o-cog-6-tooth' => 'fa-solid fa-gear',
            'heroicon-o-calendar' => 'fa-solid fa-calendar-days',
            'heroicon-o-clock' => 'fa-solid fa-clock',
            'heroicon-o-squares-2x2' => 'fa-solid fa-border-all',
            'heroicon-o-link' => 'fa-solid fa-link',
            'heroicon-o-book-open' => 'fa-solid fa-book-open',
            'heroicon-o-arrow-top-right-on-square' => 'fa-solid fa-arrow-up-right-from-square',
        ];

        if (isset($map[$icon])) {
            return $map[$icon];
        }

        if (str_starts_with($icon, 'fa-')) {
            return $icon;
        }

        return 'fa-solid fa-circle-dot';
    }

    /**
     * Design system tokens based on color selection.
     */
    public function getStyleTokensAttribute(): array
    {
        return match ($this->warna) {
            'emerald' => [
                'card_bg' => 'from-emerald-950/40 via-slate-900/80 to-slate-950/90',
                'border' => 'border-emerald-500/30 hover:border-emerald-400/80',
                'glow_ambient' => 'bg-emerald-500/15 group-hover:bg-emerald-400/25',
                'shadow_hover' => 'hover:shadow-emerald-500/20',
                'icon_box' => 'bg-gradient-to-tr from-emerald-500 to-teal-400 text-slate-950 shadow-emerald-500/30',
                'text_accent' => 'text-emerald-400 group-hover:text-emerald-300',
                'badge' => 'bg-emerald-500/15 border-emerald-400/30 text-emerald-300',
                'btn' => 'bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 shadow-emerald-500/25',
                'dot' => 'bg-emerald-400',
            ],
            'amber' => [
                'card_bg' => 'from-amber-950/40 via-slate-900/80 to-slate-950/90',
                'border' => 'border-amber-500/30 hover:border-amber-400/80',
                'glow_ambient' => 'bg-amber-500/15 group-hover:bg-amber-400/25',
                'shadow_hover' => 'hover:shadow-amber-500/20',
                'icon_box' => 'bg-gradient-to-tr from-amber-500 to-orange-400 text-slate-950 shadow-amber-500/30',
                'text_accent' => 'text-amber-400 group-hover:text-amber-300',
                'badge' => 'bg-amber-500/15 border-amber-400/30 text-amber-300',
                'btn' => 'bg-gradient-to-r from-amber-500 to-orange-400 hover:from-amber-400 hover:to-orange-300 text-slate-950 shadow-amber-500/25',
                'dot' => 'bg-amber-400',
            ],
            'indigo' => [
                'card_bg' => 'from-indigo-950/40 via-slate-900/80 to-slate-950/90',
                'border' => 'border-indigo-500/30 hover:border-indigo-400/80',
                'glow_ambient' => 'bg-indigo-500/15 group-hover:bg-indigo-400/25',
                'shadow_hover' => 'hover:shadow-indigo-500/20',
                'icon_box' => 'bg-gradient-to-tr from-indigo-500 to-violet-400 text-white shadow-indigo-500/30',
                'text_accent' => 'text-indigo-400 group-hover:text-indigo-300',
                'badge' => 'bg-indigo-500/15 border-indigo-400/30 text-indigo-300',
                'btn' => 'bg-gradient-to-r from-indigo-500 to-violet-500 hover:from-indigo-400 hover:to-violet-400 text-white shadow-indigo-500/25',
                'dot' => 'bg-indigo-400',
            ],
            'rose' => [
                'card_bg' => 'from-rose-950/40 via-slate-900/80 to-slate-950/90',
                'border' => 'border-rose-500/30 hover:border-rose-400/80',
                'glow_ambient' => 'bg-rose-500/15 group-hover:bg-rose-400/25',
                'shadow_hover' => 'hover:shadow-rose-500/20',
                'icon_box' => 'bg-gradient-to-tr from-rose-500 to-pink-400 text-white shadow-rose-500/30',
                'text_accent' => 'text-rose-400 group-hover:text-rose-300',
                'badge' => 'bg-rose-500/15 border-rose-400/30 text-rose-300',
                'btn' => 'bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-400 hover:to-pink-400 text-white shadow-rose-500/25',
                'dot' => 'bg-rose-400',
            ],
            'teal' => [
                'card_bg' => 'from-teal-950/40 via-slate-900/80 to-slate-950/90',
                'border' => 'border-teal-500/30 hover:border-teal-400/80',
                'glow_ambient' => 'bg-teal-500/15 group-hover:bg-teal-400/25',
                'shadow_hover' => 'hover:shadow-teal-500/20',
                'icon_box' => 'bg-gradient-to-tr from-teal-500 to-emerald-400 text-slate-950 shadow-teal-500/30',
                'text_accent' => 'text-teal-400 group-hover:text-teal-300',
                'badge' => 'bg-teal-500/15 border-teal-400/30 text-teal-300',
                'btn' => 'bg-gradient-to-r from-teal-500 to-emerald-400 hover:from-teal-400 hover:to-emerald-300 text-slate-950 shadow-teal-500/25',
                'dot' => 'bg-teal-400',
            ],
            'purple' => [
                'card_bg' => 'from-purple-950/40 via-slate-900/80 to-slate-950/90',
                'border' => 'border-purple-500/30 hover:border-purple-400/80',
                'glow_ambient' => 'bg-purple-500/15 group-hover:bg-purple-400/25',
                'shadow_hover' => 'hover:shadow-purple-500/20',
                'icon_box' => 'bg-gradient-to-tr from-purple-500 to-fuchsia-400 text-white shadow-purple-500/30',
                'text_accent' => 'text-purple-400 group-hover:text-purple-300',
                'badge' => 'bg-purple-500/15 border-purple-400/30 text-purple-300',
                'btn' => 'bg-gradient-to-r from-purple-500 to-fuchsia-500 hover:from-purple-400 hover:to-fuchsia-400 text-white shadow-purple-500/25',
                'dot' => 'bg-purple-400',
            ],
            'slate' => [
                'card_bg' => 'from-slate-900/70 via-slate-900/90 to-slate-950',
                'border' => 'border-slate-700/60 hover:border-slate-500',
                'glow_ambient' => 'bg-slate-700/20 group-hover:bg-slate-600/30',
                'shadow_hover' => 'hover:shadow-slate-600/20',
                'icon_box' => 'bg-gradient-to-tr from-slate-600 to-slate-400 text-slate-950 shadow-slate-600/30',
                'text_accent' => 'text-slate-300 group-hover:text-white',
                'badge' => 'bg-slate-700/30 border-slate-600/40 text-slate-300',
                'btn' => 'bg-slate-800 hover:bg-slate-700 text-white shadow-slate-800/25',
                'dot' => 'bg-slate-400',
            ],
            default => [ // sky
                'card_bg' => 'from-sky-950/40 via-slate-900/80 to-slate-950/90',
                'border' => 'border-sky-500/30 hover:border-sky-400/80',
                'glow_ambient' => 'bg-sky-500/15 group-hover:bg-sky-400/25',
                'shadow_hover' => 'hover:shadow-sky-500/20',
                'icon_box' => 'bg-gradient-to-tr from-sky-500 to-cyan-400 text-slate-950 shadow-sky-500/30',
                'text_accent' => 'text-sky-400 group-hover:text-sky-300',
                'badge' => 'bg-sky-500/15 border-sky-400/30 text-sky-300',
                'btn' => 'bg-gradient-to-r from-sky-500 to-cyan-400 hover:from-sky-400 hover:to-cyan-300 text-slate-950 shadow-sky-500/25',
                'dot' => 'bg-sky-400',
            ],
        };
    }
}
