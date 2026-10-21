<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements HasAvatar, HasName
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
     * Ambil jadwal kerja yang berlaku untuk user ini (jika tidak diset, ambil default)
     */
    public function getJadwalKerja(): ?JadwalKerja
    {
        return $this->jadwalKerja ?? JadwalKerja::getDefault();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    public function getFilamentName(): string
    {
        return $this->name ?? 'User';
    }
}
