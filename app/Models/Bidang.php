<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    protected $fillable = ['nama', 'slug', 'kepala_id'];

    protected static function booted(): void
    {
        static::saving(function (Bidang $bidang) {
            if (empty($bidang->slug) && filled($bidang->nama)) {
                $bidang->slug = \Illuminate\Support\Str::slug($bidang->nama);
            }
        });
    }

    public function kepala()
    {
        return $this->belongsTo(User::class, 'kepala_id');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class)->bukanSuperAdmin();
    }

    public function pegawai(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class)->bukanSuperAdmin();
    }

    public function pengajuanSpjs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PengajuanSpj::class, 'bidang_id');
    }

    public function masterKegiatans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasterKegiatan::class, 'bidang_id');
    }

    public function stokBarangs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StokBarang::class, 'bidang_id');
    }

    /**
     * Mendapatkan daftar jabatan yang relevan khusus untuk bidang ini.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function getJabatansTerkait(): \Illuminate\Support\Collection
    {
        $slug = $this->slug;

        $jabatans = Jabatan::query()
            ->where('nama', 'NOT LIKE', '%Kepala Dinas%')
            ->where(function ($query) use ($slug) {
                if ($slug === 'pemuda-dan-olahraga') {
                    $query->where('nama', 'LIKE', '%Pemuda%')
                        ->orWhere('nama', 'LIKE', '%Olahraga%')
                        ->orWhere('nama', 'LIKE', '%Pelatih%')
                        ->orWhere('nama', 'LIKE', '%Layanan Operasional%')
                        ->orWhere('nama', 'LIKE', '%Perkantoran%');
                } elseif ($slug === 'bidang-pemasaran-pariwisata') {
                    $query->where('nama', 'LIKE', '%Pemasaran%')
                        ->orWhere('nama', 'LIKE', '%Ekonomi Kreatif%')
                        ->orWhere('nama', 'LIKE', '%Adyatama%')
                        ->orWhere('nama', 'LIKE', '%Hubungan Msyarakat%')
                        ->orWhere('nama', 'LIKE', '%Penerjemah%')
                        ->orWhere('nama', 'LIKE', '%Layanan Operasional%')
                        ->orWhere('nama', 'LIKE', '%Perkantoran%');
                } elseif ($slug === 'pengembangan-destinasi-pariwisata') {
                    $query->where('nama', 'LIKE', '%Destinasi%')
                        ->orWhere('nama', 'LIKE', '%Konservator%')
                        ->orWhere('nama', 'LIKE', '%Teknis%')
                        ->orWhere('nama', 'LIKE', '%Layanan Operasional%')
                        ->orWhere('nama', 'LIKE', '%Perkantoran%');
                } else {
                    // Default Sekretariat / Administrasi Umum
                    $query->where('nama', 'LIKE', '%Sekretaris%')
                        ->orWhere('nama', 'LIKE', '%Kasubbag%')
                        ->orWhere('nama', 'LIKE', '%Perencana%')
                        ->orWhere('nama', 'LIKE', '%Komputer%')
                        ->orWhere('nama', 'LIKE', '%Sistem%')
                        ->orWhere('nama', 'LIKE', '%Kebijakan%')
                        ->orWhere('nama', 'LIKE', '%Operasional%')
                        ->orWhere('nama', 'LIKE', '%Perkantoran%');
                }
            })
            ->orderBy('nama')
            ->pluck('nama', 'id');

        // Jika tidak ada hasil spesifik, fallback ke seluruh jabatan kecuali Kepala Dinas
        if ($jabatans->isEmpty()) {
            return Jabatan::query()
                ->where('nama', 'NOT LIKE', '%Kepala Dinas%')
                ->orderBy('nama')
                ->pluck('nama', 'id');
        }

        return $jabatans;
    }
}
