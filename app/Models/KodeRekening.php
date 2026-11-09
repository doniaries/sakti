<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KodeRekening extends Model
{
    use HasFactory;

    protected $table = 'kode_rekenings';

    protected $fillable = [
        'akun',
        'kelompok',
        'jenis',
        'objek',
        'rincian_objek',
        'sub_rincian_objek',
        'sub_sub_rincian_objek',
        'kode_lengkap',
        'uraian',
    ];

    protected static function booted(): void
    {
        static::saving(function (KodeRekening $model) {
            if ($model->kode_lengkap) {
                $parts = explode('.', trim($model->kode_lengkap));
                $model->akun = $parts[0] ?? null;
                $model->kelompok = $parts[1] ?? null;
                $model->jenis = $parts[2] ?? null;
                $model->objek = $parts[3] ?? null;
                $model->rincian_objek = $parts[4] ?? null;
                $model->sub_rincian_objek = $parts[5] ?? null;
                $model->sub_sub_rincian_objek = $parts[6] ?? null;
            }
        });
    }

    public function masterBarangs(): HasMany
    {
        return $this->hasMany(MasterBarang::class, 'kode_rekening_id');
    }

    public function rincianAnggarans(): HasMany
    {
        return $this->hasMany(RincianAnggaranBelanja::class, 'kode_rekening_id');
    }

    public function detailPengajuanSpjs(): HasMany
    {
        return $this->hasMany(DetailPengajuanSpj::class, 'kode_rekening_id');
    }

    public function getLabelAttribute(): string
    {
        return "{$this->kode_lengkap} - {$this->uraian}";
    }

    public function getLevelAttribute(): int
    {
        return substr_count((string) $this->kode_lengkap, '.') + 1;
    }

    public function getNamaLevelAttribute(): string
    {
        return match ($this->level) {
            1 => 'Akun',
            2 => 'Kelompok',
            3 => 'Jenis',
            4 => 'Obyek',
            5 => 'Rincian Obyek',
            6 => 'Sub Rincian Obyek',
            default => 'Sub Tingkat',
        };
    }

    public function getWarnaLevelAttribute(): string
    {
        return match ($this->level) {
            1 => 'danger',
            2 => 'warning',
            3 => 'info',
            4 => 'primary',
            5 => 'gray',
            6 => 'success',
            default => 'secondary',
        };
    }

    public function getIsItemBelanjaAttribute(): bool
    {
        return $this->level >= 6;
    }

    public function scopeHanyaSubRincianObyek(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', ''))) = 5");
    }

    public function scopeHanyaRincianObjek(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $this->scopeHanyaSubRincianObyek($query);
    }

    public function scopeHanyaSubRincian(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $this->scopeHanyaSubRincianObyek($query);
    }

    /**
     * Dapatkan rincian hierarki berjenjang kode rekening sesuai standar SIPD RI
     * (Akun -> Kelompok -> Jenis -> Obyek -> Rincian Obyek -> Sub Rincian Obyek)
     */
    public function getHierarkiSipd(): array
    {
        $parts = explode('.', $this->kode_lengkap);

        // 1. Akun (5)
        $akunStr = '5 - BELANJA DAERAH';

        // 2. Kelompok (5.1 / 5.2 / 5.3 / 5.4)
        $kelompokKode = isset($parts[1]) ? ($parts[0] . '.' . $parts[1]) : '5.1';
        $kelompokModel = self::where('kode_lengkap', $kelompokKode)->first();
        $kelompokStr = $kelompokModel ? "{$kelompokModel->kode_lengkap} - {$kelompokModel->uraian}" : match ($parts[1] ?? '1') {
            '1' => '5.1 - BELANJA OPERASI',
            '2' => '5.2 - BELANJA MODAL',
            '3' => '5.3 - BELANJA TIDAK TERDUGA',
            '4' => '5.4 - BELANJA TRANSFER',
            default => "{$kelompokKode} - Kelompok Belanja",
        };

        // 3. Jenis (5.1.01, 5.1.02, dsb)
        $jenisStr = '-';
        if (isset($parts[2])) {
            $prefixJenis = $parts[0] . '.' . $parts[1] . '.' . $parts[2];
            $parent = self::where('kode_lengkap', $prefixJenis)->first();
            $jenisStr = $parent ? "{$parent->kode_lengkap} - {$parent->uraian}" : "{$prefixJenis} - Jenis Belanja";
        }

        // 4. Obyek (5.1.01.01, 5.1.02.01, dsb)
        $objekStr = '-';
        if (isset($parts[3])) {
            $prefixObjek = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.' . $parts[3];
            $parent = self::where('kode_lengkap', $prefixObjek)->first();
            $objekStr = $parent ? "{$parent->kode_lengkap} - {$parent->uraian}" : "{$prefixObjek} - Obyek Belanja";
        }

        // 5. Rincian Obyek (5.1.01.01.01 atau 5.1.02.01.001)
        $rincianObjekStr = '-';
        if (isset($parts[4])) {
            $p4 = $parts[4];
            $candidates = [
                $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.' . $parts[3] . '.' . $p4,
                $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.' . $parts[3] . '.' . str_pad(ltrim($p4, '0'), 2, '0', STR_PAD_LEFT),
                $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.' . $parts[3] . '.' . str_pad(ltrim($p4, '0'), 3, '0', STR_PAD_LEFT),
            ];
            $parent = self::whereIn('kode_lengkap', $candidates)->first();
            $rincianObjekStr = $parent ? "{$parent->kode_lengkap} - {$parent->uraian}" : "{$parts[0]}.{$parts[1]}.{$parts[2]}.{$parts[3]}.{$p4} - Rincian Obyek Belanja";
        }

        // 6. Sub Rincian Obyek (Item transaksi SPJ / RKA)
        $subRincianObyekStr = "{$this->kode_lengkap} - {$this->uraian}";

        return [
            'akun' => $akunStr,
            'kelompok' => $kelompokStr,
            'jenis' => $jenisStr,
            'objek' => $objekStr,
            'rincian_objek' => $rincianObjekStr,
            'sub_rincian_obyek' => $subRincianObyekStr,
            // Backwards-compatibility aliases
            'jenis_objek' => $rincianObjekStr,
            'sub_rincian' => $subRincianObyekStr,
        ];
    }
}

