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

    /**
     * Boot model events untuk pembersihan file avatar lama secara otomatis
     */
    protected static function booted(): void
    {
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

            if (Storage::disk('public')->exists($this->avatar_url)) {
                return Storage::disk('public')->url($this->avatar_url);
            }
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?? 'User') . '&background=0284c7&color=fff&size=160';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return (!empty($this->avatar_url) && Storage::disk('public')->exists($this->avatar_url))
            ? Storage::disk('public')->url($this->avatar_url)
            : null;
    }

    public function getFilamentName(): string
    {
        return $this->name ?? 'User';
    }

    /**
     * Hak akses Panel Admin Filament: Khusus super_admin dan admin
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin']);
    }
}
