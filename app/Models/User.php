<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\DeletesOldFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Permission\Traits\HasRoles;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Support\Facades\Storage;

/**
 * @method static \App\Models\User|null find(mixed $id, array $columns = [])
 * @method static \Illuminate\Database\Eloquent\Builder where(string|array|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder whereNotNull(string $column)
 * @method static \Illuminate\Database\Eloquent\Builder whereNull(string $column)
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 */
class User extends Authenticatable implements HasAvatar, HasName, FilamentUser
{
    use HasPushSubscriptions;

    public function sendWebPush(string $title, string $body, string $url = '/mobile/dashboard'): void
    {
        if (\App\Models\PengaturanAplikasi::getPengaturan()->mode_simulasi && ! $this->hasAnyRole(['super_admin', 'superadmin'])) {
            return;
        }

        \App\Services\PushNotificationService::sendFcmToUsers([$this], $title, $body, ['url' => $url]);

        if (! config('webpush.vapid.public_key') || ! $this->pushSubscriptions()->exists()) {
            return;
        }

        try {
            $this->notify(new \App\Notifications\WebPushNotification($title, $body, $url));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('WebPush error: ' . $e->getMessage());
        }
    }

    /**
     * Daftar field kepegawaian pokok yang belum diisi oleh pegawai.
     *
     * @return array<int, string>
     */
    public function missingKepegawaianFields(): array
    {
        $fields = [
            'nip' => 'NIP',
            'nik' => 'NIK',
            'no_hp' => 'No. HP',
            'bidang_id' => 'Bidang',
            'jabatan_id' => 'Jabatan',
            'status_kepegawaian' => 'Status Kepegawaian',
        ];

        $missing = [];
        foreach ($fields as $column => $label) {
            if (blank($this->{$column})) {
                $missing[] = $label;
            }
        }

        return $missing;
    }
    use SoftDeletes;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use DeletesOldFiles, HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected function fileAttributes(): array
    {
        return ['avatar_url', 'file_ttd'];
    }

    /**
     * Cari user berdasarkan email login utama atau email resmi (@sijunjung.go.id).
     */
    public static function findByLoginEmail(string $email): ?self
    {
        $normalized = strtolower(trim($email));
        if ($normalized === '') {
            return null;
        }

        return static::query()
            ->where(function ($query) use ($normalized): void {
                $query->whereRaw('LOWER(email) = ?', [$normalized])
                    ->orWhereRaw('LOWER(email_resmi) = ?', [$normalized]);
            })
            ->first();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_resmi',
        'password',
        'nip',
        'status_kepegawaian',
        'nomor_absensi',
        'aktif',
        'jabatan_id',
        'anggaran_role',
        'jabatan_tim_kreatif',
        'is_pengelola_aset',
        'bidang_id',
        'jf_id',
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
        'kuota_cuti',
        'avatar_url',
        'file_ttd',
        'nik',
        'no_kk',
        'nama_pasangan',
        'nama_anak',
        'no_hp',
        'no_bpjs',
        'gelar_depan',
        'gelar_belakang',
        'agama',
        'alamat',
        'no_telp',
        'google_id',
        'device_id',
        'device_name',
        'device_bound_at',
        'fcm_token',
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
            'device_bound_at' => 'datetime',
            'password' => 'hashed',
            'tanggal_lahir' => 'date',
            'tanggal_npwp' => 'date',
            'pangkat_golongan' => \App\Enums\GolonganRuang::class,
            'status_kepegawaian' => \App\Enums\StatusKepegawaian::class,
            'jenis_kelamin' => \App\Enums\JenisKelamin::class,
            'eselon' => \App\Enums\Eselon::class,
            'anggaran_role' => \App\Enums\AnggaranRole::class,
            'jabatan_tim_kreatif' => \App\Enums\JabatanTimKreatif::class,
            'is_pengelola_aset' => 'boolean',
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

    public function aktivitas(): HasMany
    {
        return $this->hasMany(Aktivitas::class);
    }

    public function riwayatPendidikan(): HasMany
    {
        return $this->hasMany(RiwayatPendidikan::class)->orderBy('id');
    }

    public function riwayatGolongan(): HasMany
    {
        return $this->hasMany(RiwayatGolongan::class)->orderByDesc('tmt_golongan')->orderByDesc('id');
    }

    public function riwayatJabatan(): HasMany
    {
        return $this->hasMany(RiwayatJabatan::class)->orderByDesc('tmt_jabatan')->orderByDesc('id');
    }

    public function riwayatSertifikat(): HasMany
    {
        return $this->hasMany(RiwayatSertifikat::class)->orderByDesc('tanggal_terbit')->orderByDesc('id');
    }


    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function jfPenempatan()
    {
        return $this->belongsTo(self::class, 'jf_id');
    }

    public function stafJf()
    {
        return $this->hasMany(self::class, 'jf_id');
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
     * Total pagu kuota cuti tahunan pegawai
     */
    public function getTotalKuotaCutiAttribute(): int
    {
        return (int) ($this->kuota_cuti ?? 12);
    }

    /**
     * Jumlah hari cuti yang telah diajukan/disetujui pada tahun berjalan
     */
    public function getCutiTerpakaiAttribute(): int
    {
        return (int) $this->pengajuanIzins()
            ->whereHas('jenisIzin', fn($q) => $q->where('kategori', 'cuti'))
            ->whereIn('status', ['disetujui', 'menunggu'])
            ->whereYear('tanggal_mulai', date('Y'))
            ->sum('jumlah_hari');
    }

    /**
     * Sisa kuota cuti tahunan pegawai
     */
    public function getSisaCutiAttribute(): int
    {
        return max(0, $this->total_kuota_cuti - $this->cuti_terpakai);
    }

    /**
     * Scope untuk mengecualikan akun murni IT/SuperAdmin & Admin Sistem (yang bukan pegawai dinas).
     * Pegawai definitif yang juga memegang role manajerial tetap disertakan sebagai pegawai.
     */
    public function scopeBukanSuperAdmin(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNotIn('email', ['superadmin@gmail.com', 'adminkeuangan@gmail.com', 'operator.sakti@sijunjung.go.id'])
            ->where(function ($q) {
                $q->whereDoesntHave('roles', function ($sub) {
                    $sub->whereIn('name', ['super_admin', 'admin_kepegawaian', 'admin_keuangan']);
                })->orWhereHas('roles', function ($sub) {
                    $sub->whereIn('name', ['pegawai', 'pegawai_kontrak']);
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
    public function getGelarBelakangAttribute(?string $value): ?string
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
    public function getGelarDepanAttribute(?string $value): ?string
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
            'pegawai_kontrak',
            'panel_user',
            'pa',
            'ppk',
            'pptk',
            'kabid',
            'kepala_bidang',
            'sekretaris',
            'pimpinan',
            'jf',
            'bendahara',
        ]);
    }

    /**
     * Hanya Super Admin yang diizinkan melakukan impersonate (login sebagai pegawai lain).
     */
    public function canImpersonate(): bool
    {
        return (bool) $this->aktif && $this->hasRole('super_admin');
    }

    /**
     * Akun Super Admin tidak boleh di-impersonate; hanya akun aktif non-superadmin yang bisa.
     */
    public function canBeImpersonated(): bool
    {
        return (bool) $this->aktif && ! $this->hasRole('super_admin');
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
        $roleValue = $this->anggaran_role instanceof \App\Enums\AnggaranRole
            ? $this->anggaran_role->value
            : $this->anggaran_role;

        return $this->hasAnyRole(['super_admin', 'admin', 'admin_keuangan', 'pa', 'ppk', 'bendahara'])
            || in_array(strtoupper((string) $roleValue), ['PA', 'PPK', 'BENDAHARA']);
    }

    /**
     * Cek apakah user bertindak sebagai Pejabat Pelaksana Teknis Kegiatan (PPTK)
     */
    public function isPptk(): bool
    {
        $roleValue = $this->anggaran_role instanceof \App\Enums\AnggaranRole
            ? $this->anggaran_role->value
            : $this->anggaran_role;

        return $this->hasRole('pptk')
            || strtoupper((string) $roleValue) === 'PPTK'
            || Bidang::where('kepala_id', $this->id)->exists();
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
     * Apakah user bertugas sebagai Administrasi pada Tim Kreatif Dinas.
     */
    public function isAdminTimKreatif(): bool
    {
        $jabatan = $this->jabatan_tim_kreatif instanceof \App\Enums\JabatanTimKreatif
            ? $this->jabatan_tim_kreatif
            : \App\Enums\JabatanTimKreatif::tryFrom((string) $this->jabatan_tim_kreatif);

        return $jabatan === \App\Enums\JabatanTimKreatif::ADMINISTRASI;
    }

    /**
     * Apakah user bertugas sebagai Kepala Bidang (via role, kepala bidang definitif, atau nama jabatan).
     */
    public function isKepalaBidang(): bool
    {
        return $this->hasAnyRole(['kabid', 'kepala_bidang'])
            || Bidang::where('kepala_id', $this->id)->exists()
            || str_contains(strtolower((string) ($this->jabatan?->nama ?? '')), 'kepala bidang');
    }

    /**
     * Apakah user berhak membuat & mengubah Surat Tugas:
     * Super Admin, Administrator, Administrasi Tim Kreatif, dan Kepala Bidang.
     */
    public function canManageSuratTugas(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin'])
            || $this->isAdminTimKreatif()
            || $this->isKepalaBidang();
    }

    /**
     * Apakah user berhak membuat, mengubah, dan menghapus data Aset & Berita Acara BMD:
     * hanya Super Admin dan Pengelola Aset.
     */
    public function canManageAset(): bool
    {
        return $this->hasRole('super_admin') || (bool) $this->is_pengelola_aset;
    }

    /**
     * Apakah user berhak melihat data Aset & Berita Acara BMD (rahasia dinas):
     * Super Admin, Pengelola Aset, Kadis (PA), Kabid, dan JF.
     */
    public function canViewAset(): bool
    {
        if ($this->canManageAset()) {
            return true;
        }

        if ($this->hasAnyRole(['pa', 'kadis', 'jf']) || $this->isKepalaBidang()) {
            return true;
        }

        $jabatan = strtolower((string) ($this->jabatan?->nama ?? ''));

        return str_contains($jabatan, 'kepala dinas')
            || str_contains($jabatan, 'fungsional')
            || str_contains($jabatan, 'ahli ');
    }

    /**
     * Apakah user berhak mengesahkan Surat Tugas dengan TTE internal SAKTI.
     */
    public function canSignSuratTugas(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin', 'pa', 'sekretaris', 'kabid']);
    }

    /**
     * URL direct chat WhatsApp
     */
    public function getWhatsappUrlAttribute(): ?string
    {
        $wa = $this->whatsapp_number;
        return $wa ? "https://wa.me/{$wa}" : null;
    }

    /**
     * Kirim notifikasi reset password menggunakan URL Filament Panel Admin
     */
    public function sendPasswordResetNotification($token): void
    {
        $notification = app(\Filament\Auth\Notifications\ResetPassword::class, ['token' => $token]);
        $notification->url = \Filament\Facades\Filament::getResetPasswordUrl($token, $this);

        $this->notify($notification);
    }

    /**
     * Daftar perangkat terdaftar milik pegawai (Android APK, PWA, PC).
     */
    public function devices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    /**
     * Verifikasi atau ikat perangkat pegawai (maksimal sesuai pengaturan aplikasi, default 3 device).
     * Mencegah satu HP Android digunakan oleh akun pegawai lain (anti-titip absen).
     *
     * @return array{ok: bool, message?: string}
     */
    public function bindOrVerifyDevice(string $deviceId, ?string $deviceName = null, ?string $ipAddress = null): array
    {
        $cleanId = trim($deviceId);
        $cleanName = $deviceName ? trim($deviceName) : null;
        $ip = $ipAddress ?? request()?->ip();

        if ($cleanId === '') {
            return [
                'ok' => false,
                'message' => 'Identitas perangkat (device_id) tidak valid.',
            ];
        }

        $isBrowser = ($cleanName !== null && str_contains(strtolower($cleanName), 'browser'))
            || str_starts_with(strtolower($cleanId), 'web-')
            || str_starts_with(strtolower($cleanId), 'browser-');
        $platform = $isBrowser ? 'pwa' : 'android';
        $displayName = $cleanName ?: ($isBrowser ? 'Browser Mobile / PC' : 'Perangkat Android');
        $isSuperAdminBypass = strtolower((string) $this->email) === 'superadmin@gmail.com'
            || $this->hasRole('super_admin');

        if ($isSuperAdminBypass) {
            if (\Illuminate\Support\Facades\Schema::hasTable('user_devices')) {
                $this->devices()->updateOrCreate(
                    ['device_id' => $cleanId],
                    [
                        'device_name' => $displayName,
                        'platform' => $platform,
                        'ip_address' => $ip,
                        'last_login_at' => now(),
                        'is_active' => true,
                    ]
                );
            }

            return ['ok' => true];
        }

        // Migrasi otomatis ikatan perangkat lama (kolom users.device_id) ke tabel user_devices
        if (! empty($this->device_id) && \Illuminate\Support\Facades\Schema::hasTable('user_devices')) {
            $legacyIsBrowser = str_contains(strtolower((string) $this->device_name), 'browser')
                || str_starts_with(strtolower((string) $this->device_id), 'web-')
                || str_starts_with(strtolower((string) $this->device_id), 'browser-');

            $this->devices()->firstOrCreate(
                ['device_id' => (string) $this->device_id],
                [
                    'device_name' => $this->device_name ?: ($legacyIsBrowser ? 'Browser Mobile' : 'Perangkat Android'),
                    'platform' => $legacyIsBrowser ? 'pwa' : 'android',
                    'last_login_at' => $this->device_bound_at ?? now(),
                    'is_active' => true,
                ]
            );
        }

        // Anti-titip absen: 1 HP Android fisik tidak boleh digunakan oleh akun pegawai lain
        if ($platform === 'android' && \Illuminate\Support\Facades\Schema::hasTable('user_devices')) {
            $otherBinding = UserDevice::query()
                ->with('user:id,name')
                ->where('device_id', $cleanId)
                ->where('user_id', '!=', $this->id)
                ->where('is_active', true)
                ->first();

            if ($otherBinding) {
                $ownerName = $otherBinding->user?->name ?? 'pegawai lain';
                \App\Services\ActivityLogger::log(
                    'device_blocked',
                    'Keamanan Perangkat',
                    "Percobaan login ditolak: perangkat {$displayName} sudah terikat ke {$ownerName}.",
                    $this,
                    ['device_id' => $cleanId, 'device_name' => $displayName, 'platform' => $platform],
                    $this
                );

                return [
                    'ok' => false,
                    'message' => "Perangkat {$displayName} sudah terdaftar atas nama {$ownerName}. Satu HP Android tidak dapat digunakan oleh akun pegawai lain.",
                ];
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('user_devices')) {
            $existing = $this->devices()->where('device_id', $cleanId)->first();

            if ($existing) {
                if (! $existing->is_active) {
                    return [
                        'ok' => false,
                        'message' => "Perangkat {$displayName} telah dinonaktifkan oleh Admin Kepegawaian.",
                    ];
                }

                $existing->forceFill([
                    'device_name' => $displayName,
                    'platform' => $platform,
                    'ip_address' => $ip,
                    'last_login_at' => now(),
                ])->save();

                if ($platform === 'android' || empty($this->device_id)) {
                    $this->forceFill([
                        'device_id' => $cleanId,
                        'device_name' => $displayName,
                        'device_bound_at' => $this->device_bound_at ?? now(),
                    ])->save();
                }

                return ['ok' => true];
            }

            $maxDevices = max(1, (int) (PengaturanAplikasi::getPengaturan()->max_devices_per_user ?? 3));
            $activeCount = $this->devices()->where('is_active', true)->count();

            if ($activeCount >= $maxDevices) {
                return [
                    'ok' => false,
                    'message' => "Batas maksimal {$maxDevices} perangkat per akun telah tercapai. Hapus atau reset salah satu perangkat melalui Admin Kepegawaian.",
                ];
            }

            $this->devices()->create([
                'device_id' => $cleanId,
                'device_name' => $displayName,
                'platform' => $platform,
                'ip_address' => $ip,
                'last_login_at' => now(),
                'is_active' => true,
            ]);

            \App\Services\ActivityLogger::log(
                'register_device',
                'Keamanan Perangkat',
                "Perangkat baru terdaftar: {$displayName} ({$platform}).",
                $this,
                ['device_id' => $cleanId, 'device_name' => $displayName, 'platform' => $platform, 'ip' => $ip],
                $this
            );

            if ($platform === 'android' || empty($this->device_id)) {
                $this->forceFill([
                    'device_id' => $cleanId,
                    'device_name' => $displayName,
                    'device_bound_at' => now(),
                ])->save();
            }

            return ['ok' => true];
        }

        // Fallback jika tabel user_devices belum dimigrasi
        if (empty($this->device_id) || hash_equals((string) $this->device_id, $cleanId)) {
            $this->forceFill([
                'device_id' => $cleanId,
                'device_name' => $displayName,
                'device_bound_at' => $this->device_bound_at ?? now(),
            ])->save();

            return ['ok' => true];
        }

        $boundLabel = $this->device_name ?: 'perangkat lain';

        return [
            'ok' => false,
            'message' => "Akun ini sudah terhubung dengan {$boundLabel}. Satu akun hanya dapat digunakan pada satu perangkat. Hubungi Admin Kepegawaian untuk reset perangkat.",
        ];
    }

    /**
     * Reset seluruh ikatan perangkat mobile agar pegawai dapat login di perangkat baru.
     */
    public function resetDeviceBinding(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('user_devices')) {
            $this->devices()->delete();
        }

        $this->forceFill([
            'device_id' => null,
            'device_name' => null,
            'device_bound_at' => null,
        ])->save();

        $this->tokens()->where('name', 'mobile-app')->delete();

        \App\Services\ActivityLogger::log(
            'reset_device',
            'Keamanan Perangkat',
            "Seluruh ikatan perangkat mobile untuk {$this->name} telah direset.",
            $this
        );
    }
}
