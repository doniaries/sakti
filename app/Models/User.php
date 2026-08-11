<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
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

    public function absensiMobile(): HasMany
    {
        return $this->hasMany(AbsensiMobile::class);
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
}
