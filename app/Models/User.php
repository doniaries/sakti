<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Support\Facades\Storage;

/**
 * @method static \App\Models\User|null find(mixed $id, array $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder where(string|array|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder whereNotNull(string $column)
 * @method static \Illuminate\Database\Eloquent\Builder whereNull(string $column)
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 */
class User extends Authenticatable implements HasAvatar, HasName, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'nomor_absensi',
        'aktif',
        'jabatan_id',
        'anggaran_role',
        'bidang_id',
        'tempat_lahir',
        'tanggal_lahir',
        'pangkat_golongan',
        'tmt_pangkat',
        'pendidikan',
        'jenis_kelamin',
        'eselon',
        'atasan_id',
        'jadwal_kerja_id',
        'status_kerja',
        'avatar_url',
        'file_ttd',
        'nik',
        'no_kk',
        'no_hp',
        'no_bpjs',
        'gelar_depan',
        'gelar_belakang',
        'agama',
        'alamat',
        'no_telp',
        'email_pribadi',
        'npwp',
        'tanggal_npwp',
        'no_taspen',
        'jenjang_pendidikan',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tanggal_lahir' => 'date',
            'tanggal_npwp' => 'date',
            'pangkat_golongan' => \App\Enums\GolonganRuang::class,
            'jenis_kelamin' => \App\Enums\JenisKelamin::class,
            'eselon' => \App\Enums\Eselon::class,
            'anggaran_role' => \App\Enums\AnggaranRole::class,
        ];
    }

    public function logAbsensi(): HasMany
    {
        return $this->hasMany(LogAbsensi::class);
    }

    public function absensiHarian(): HasMany
    {
        return $this->hasMany(AbsensiHarian::class);
    }

    public function riwayatPendidikan(): HasMany
    {
        return $this->hasMany(RiwayatPendidikan::class)->orderByRaw("FIELD(jenjang, 'S3', 'S2', 'S1', 'D4', 'D3', 'D2', 'D1', 'SLTA', 'SLTP', 'SD')");
    }

    public function riwayatGolongan(): HasMany
    {
        return $this->hasMany(RiwayatGolongan::class)->orderByDesc('tmt_golongan')->orderByDesc('id');
    }

    public function riwayatJabatan(): HasMany
    {
        return $this->hasMany(RiwayatJabatan::class)->orderByDesc('tmt_jabatan')->orderByDesc('id');
    }


    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    public function bawahan()
    {
        return $this->hasMany(User::class, 'atasan_id');
    }

    public function jadwalKerja(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(JadwalKerja::class);
    }

    public function pengajuanIzins(): HasMany
    {
        return $this->hasMany(PengajuanIzin::class);
    }

    public function pengajuanSpjs(): HasMany
    {
        return $this->hasMany(PengajuanSpj::class, 'user_id');
    }

    /**
     * Scope untuk mengecualikan akun murni IT/SuperAdmin sistem (yang bukan pegawai dinas).
     * Pegawai definitif yang juga memegang role super_admin tetap disertakan sebagai pegawai.
     */
    public function scopeBukanSuperAdmin(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('email', '!=', 'superadmin@gmail.com')
            ->where(function ($q) {
                $q->whereDoesntHave('roles', function ($sub) {
                    $sub->where('name', 'super_admin');
                })->orWhereHas('roles', function ($sub) {
                    $sub->where('name', 'pegawai');
                });
            });
    }

    /**
     * Boot model events untuk pembersihan file avatar lama dan auto-extract gelar kepegawaian
     */
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            // Otomatis sinkronkan gelar_belakang dari nama jika belum diisi
            if (empty($user->gelar_belakang) && !empty($user->name) && str_contains($user->name, ',')) {
                $parts = explode(',', $user->name);
                array_shift($parts);
                $user->gelar_belakang = trim(implode(', ', array_map('trim', $parts)));
            }

            // Otomatis sinkronkan gelar_depan dari nama jika belum diisi
            if (empty($user->gelar_depan) && !empty($user->name) && preg_match('/^(Dr\.|Dra\.|Drs\.|Ir\.|Prof\.|H\.|Hj\.|Apt\.)\s+/i', $user->name, $m)) {
                $user->gelar_depan = trim($m[1]);
            }
        });

        static::updating(function (User $user) {
            if ($user->isDirty('avatar_url')) {
                $oldAvatar = $user->getOriginal('avatar_url');
                if (!empty($oldAvatar) && Storage::disk('public')->exists($oldAvatar)) {
                    Storage::disk('public')->delete($oldAvatar);
                }
            }
        });

        static::deleting(function (User $user) {
            if (!empty($user->avatar_url) && Storage::disk('public')->exists($user->avatar_url)) {
                Storage::disk('public')->delete($user->avatar_url);
            }
        });
    }

    /**
     * Accessor otomatis gelar belakang jika kosong di kolom namun ada pada nama
     */
    public function getGelarBelakangAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        if (!empty($this->attributes['name']) && str_contains($this->attributes['name'], ',')) {
            $parts = explode(',', $this->attributes['name']);
            array_shift($parts);
            return trim(implode(', ', array_map('trim', $parts)));
        }

        return null;
    }

    /**
     * Accessor otomatis gelar depan jika kosong di kolom namun ada pada nama
     */
    public function getGelarDepanAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        if (!empty($this->attributes['name']) && preg_match('/^(Dr\.|Dra\.|Drs\.|Ir\.|Prof\.|H\.|Hj\.|Apt\.)\s+/i', $this->attributes['name'], $m)) {
            return trim($m[1]);
        }

        return null;
    }

    /**
     * Ambil jadwal kerja yang berlaku untuk user ini (jika tidak diset, ambil default)
     */
    public function getJadwalKerja(): ?JadwalKerja
    {
        return $this->jadwalKerja ?? JadwalKerja::getDefault();
    }

    /**
     * URL Foto Avatar Pegawai (dengan fallback otomatis ke UI Avatars)
     */
    public function getAvatarImageUrlAttribute(): string
    {
        if (!empty($this->avatar_url)) {
            if (str_starts_with($this->avatar_url, 'http://') || str_starts_with($this->avatar_url, 'https://')) {
                return $this->avatar_url;
            }

            $cleanPath = ltrim($this->avatar_url, '/');
            if (Storage::disk('public')->exists($cleanPath)) {
                $storageLink = public_path('storage');
                if (is_link($storageLink) || is_dir($storageLink)) {
                    return asset('storage/' . $cleanPath);
                }
                return url('/media-file/' . $cleanPath);
            }
        }

        $cleanName = (string) preg_replace('/[^a-zA-Z0-9\s]/', '', $this->name ?? 'User');
        $initials = collect(explode(' ', trim($cleanName)))
            ->filter()
            ->take(2)
            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');

        return 'https://ui-avatars.com/api/?name=' . urlencode($initials ?: 'U') . '&background=0284c7&color=ffffff&size=160';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (!empty($this->avatar_url)) {
            if (str_starts_with($this->avatar_url, 'http://') || str_starts_with($this->avatar_url, 'https://')) {
                return $this->avatar_url;
            }

            $cleanPath = ltrim($this->avatar_url, '/');
            if (Storage::disk('public')->exists($cleanPath)) {
                $storageLink = public_path('storage');
                if (is_link($storageLink) || is_dir($storageLink)) {
                    return asset('storage/' . $cleanPath);
                }
                return url('/media-file/' . $cleanPath);
            }
        }

        // Hindari return null agar Filament v4/v5 tidak memanggil UiAvatarsProvider bawaan yang mengirim oklch (error 400 Bad Request)
        $cleanName = (string) preg_replace('/[^a-zA-Z0-9\s]/', '', $this->name ?? 'User');
        $initials = collect(explode(' ', trim($cleanName)))
            ->filter()
            ->take(2)
            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');

        return 'https://ui-avatars.com/api/?name=' . urlencode($initials ?: 'U') . '&background=0284c7&color=ffffff&size=160';
    }

    public function getFilamentName(): string
    {
        return $this->name ?? 'User';
    }

    /**
     * Hak akses Panel Admin Filament dikendalikan melalui Filament Shield Roles & Policies
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->aktif && $this->hasAnyRole([
            'super_admin',
            'admin',
            'admin_kepegawaian',
            'admin_keuangan',
            'pegawai',
            'panel_user',
            'pa',
            'ppk',
            'pptk',
            'jf',
            'bendahara',
        ]);
    }

    /**
     * Format nomor WhatsApp terstandarisasi internasional (diawali 62)
     */
    public function getWhatsappNumberAttribute(): ?string
    {
        if (empty($this->no_hp)) {
            return null;
        }

        $clean = preg_replace('/[^0-9]/', '', (string) $this->no_hp);
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }

        return !empty($clean) ? $clean : null;
    }

    /**
     * Apakah user berhak melihat seluruh data lintas bidang untuk urusan keuangan / anggaran (Pemeriksa)
     * PPK, PA, Bendahara, Admin Keuangan, Admin Dinas, dan Super Admin dapat melihat seluruh bidang sebagai pemeriksa.
     */
    public function canViewAllBidangKeuangan(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin', 'admin_keuangan', 'pa', 'ppk', 'bendahara']);
    }

    /**
     * Apakah user berhak melihat seluruh data lintas bidang untuk urusan kepegawaian
     * Kasubag Kepegawaian (Admin Kepegawaian), PA (Kepala Dinas), dan Super Admin dapat melihat seluruh bidang.
     */
    public function canViewAllBidangKepegawaian(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian', 'pa']);
    }

    /**
     * URL direct chat WhatsApp
     */
    public function getWhatsappUrlAttribute(): ?string
    {
        $wa = $this->whatsapp_number;
        return $wa ? "https://wa.me/{$wa}" : null;
    }
}
