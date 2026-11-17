<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanAplikasi extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_aplikasis';

    protected $fillable = [
        'nama_aplikasi',
        'sub_nama',
        'nama_dinas',
        'kode_instansi',
        'sub_unit_organisasi',
        'kepala_id',
        'nama_kepala_dinas',
        'latitude_kantor',
        'longitude_kantor',
        'radius_meter',
        'mode_simulasi',
        'simulasi_jam_aktif',
        'alamat_kantor',
        'logo',
        'versi_aplikasi',
        'tahun_anggaran_aktif',
        'pembuat_aplikasi',
        'kontak_bantuan',
        'deskripsi',
        'modul_aktif',
    ];

    /**
     * Daftar default status aktif modul sistem (Single Source of Truth untuk Filament, Mobile Livewire, dan API/Flutter).
     *
     * @var array<string, bool>
     */
    public const DEFAULT_MODULES = [
        'lhk' => false,
        'izin' => true,
        'wfh' => true,
        'surat_tugas' => true,
        'spj' => true,
    ];

    public function kepala(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'kepala_id');
    }

    /**
     * Alias accessor untuk kompatibilitas nama_instansi -> nama_dinas
     */
    public function getNamaInstansiAttribute(): ?string
    {
        return $this->nama_dinas;
    }

    public function setNamaInstansiAttribute($value): void
    {
        $this->attributes['nama_dinas'] = $value;
    }

    public function getNamaDinasAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        return \App\Models\Instansi::query()->value('nama_dinas') ?? 'DINAS PARIWISATA, PEMUDA DAN OLAH RAGA';
    }

    public function getLogoAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        return \App\Models\Instansi::query()->value('logo');
    }

    public function getAlamatKantorAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        return \App\Models\Instansi::query()->value('alamat');
    }

    public function getKodeInstansiAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        return \App\Models\Instansi::query()->value('kode_instansi') ?? '3.26.2.19.0.00.01.0000';
    }

    public function getNamaKepalaDinasAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        return \App\Models\Instansi::query()->value('nama_kepala_dinas');
    }

    public function getKepalaIdAttribute($value): ?int
    {
        if (!empty($value)) {
            return (int) $value;
        }

        return \App\Models\Instansi::query()->value('kepala_id');
    }

    protected static function booted(): void
    {
        static::saved(function (self $pengaturan) {
            // Sinkronisasi data ke model Instansi demi kompatibilitas penuh jika ada field yang terisi
            try {
                $instansiData = array_filter([
                    'nama_dinas' => $pengaturan->attributes['nama_dinas'] ?? null,
                    'kode_instansi' => $pengaturan->attributes['kode_instansi'] ?? null,
                    'sub_unit_organisasi' => $pengaturan->attributes['sub_unit_organisasi'] ?? null,
                    'nama_kepala_dinas' => $pengaturan->attributes['nama_kepala_dinas'] ?? null,
                    'alamat' => $pengaturan->attributes['alamat_kantor'] ?? null,
                    'logo' => $pengaturan->attributes['logo'] ?? null,
                    'kepala_id' => $pengaturan->attributes['kepala_id'] ?? null,
                ], fn($val) => !is_null($val) && $val !== '');

                if (!empty($instansiData)) {
                    \App\Models\Instansi::updateOrCreate(['id' => 1], $instansiData);
                }
            } catch (\Throwable $e) {
                // Abaikan jika tabel instansis belum siap
            }

            \Illuminate\Support\Facades\Cache::forget('pengaturan_tahun_anggaran_aktif');
            \Illuminate\Support\Facades\Cache::forget('pengaturan_modul_aktif');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('pengaturan_tahun_anggaran_aktif');
            \Illuminate\Support\Facades\Cache::forget('pengaturan_modul_aktif');
        });
    }

    protected $casts = [
        'latitude_kantor' => 'float',
        'longitude_kantor' => 'float',
        'radius_meter' => 'integer',
        'tahun_anggaran_aktif' => 'integer',
        'modul_aktif' => 'array',
    ];

    /**
     * Peta status aktif seluruh modul (di-cache untuk performa).
     *
     * @return array<string, bool>
     */
    public static function getModulAktifMap(): array
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('pengaturan_modul_aktif', function (): array {
            try {
                $raw = static::query()->value('modul_aktif');
                $decoded = is_array($raw) ? $raw : (is_string($raw) ? json_decode($raw, true) : []);
                if (! is_array($decoded)) {
                    $decoded = [];
                }

                $result = self::DEFAULT_MODULES;
                foreach (self::DEFAULT_MODULES as $key => $default) {
                    if (array_key_exists($key, $decoded)) {
                        $result[$key] = (bool) $decoded[$key];
                    }
                }

                return $result;
            } catch (\Throwable $e) {
                return self::DEFAULT_MODULES;
            }
        });
    }

    /**
     * Cek apakah suatu modul sedang diaktifkan di sistem.
     */
    public static function isModuleEnabled(string $module): bool
    {
        $map = static::getModulAktifMap();

        return (bool) ($map[$module] ?? true);
    }

    /**
     * Dapatkan tahun anggaran aktif sistem (di-cache untuk performa maksimal 0 ms overhead)
     */
    public static function getTahunAnggaranAktif(): int
    {
        return (int) \Illuminate\Support\Facades\Cache::rememberForever('pengaturan_tahun_anggaran_aktif', function (): int {
            try {
                $tahun = (int) static::query()->value('tahun_anggaran_aktif');
                return $tahun > 0 ? $tahun : (int) date('Y');
            } catch (\Throwable $e) {
                return (int) date('Y');
            }
        });
    }

    /**
     * Singleton helper untuk mendapatkan pengaturan aplikasi aktif
     */
    public static function getPengaturan(): self
    {
        /** @var self|null $pengaturan */
        $pengaturan = static::query()->first();

        $instansi = \App\Models\Instansi::query()->first();

        if (! $pengaturan) {
            $pengaturan = static::query()->create([
                'nama_aplikasi' => 'Sakti',
                'sub_nama' => 'Sistem Administrasi Kinerja Terintegrasi',
                'nama_dinas' => $instansi?->nama_dinas ?? 'DINAS PARIWISATA, PEMUDA DAN OLAH RAGA',
                'kode_instansi' => $instansi?->kode_instansi ?? '3.26.2.19.0.00.01.0000',
                'sub_unit_organisasi' => $instansi?->sub_unit_organisasi ?? '-',
                'kepala_id' => $instansi?->kepala_id,
                'nama_kepala_dinas' => $instansi?->nama_kepala_dinas ?? 'AFRINELDI, SH',
                'latitude_kantor' => -0.69752300,
                'longitude_kantor' => 100.98547200,
                'radius_meter' => 50,
                'alamat_kantor' => $instansi?->alamat ?? 'Gedung Bersama Lt. III, Jl. Prof. M. Yamin, SH No. 1, Muaro Sijunjung, Sumatera Barat',
                'logo' => $instansi?->logo,
                'versi_aplikasi' => 'v1.0.0',
                'pembuat_aplikasi' => 'Don Borland',
                'kontak_bantuan' => 'helpdesk@sakti.sijunjung.cloud',
                'deskripsi' => 'Sistem Administrasi Kinerja Terintegrasi',
            ]);
        } else {
            // Ambil nama_dinas dari instansi jika belum terisi
            if (empty($pengaturan->nama_dinas) && $instansi?->nama_dinas) {
                $pengaturan->nama_dinas = $instansi->nama_dinas;
                $pengaturan->saveQuietly();
            }
        }

        return $pengaturan;
    }
}
