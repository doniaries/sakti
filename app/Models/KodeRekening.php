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
                $parts = explode('.', $model->kode_lengkap);
                $model->akun = filled($model->akun) ? $model->akun : ($parts[0] ?? '5');
                $model->kelompok = filled($model->kelompok) ? $model->kelompok : ($parts[1] ?? '1');
                $model->jenis = filled($model->jenis) ? $model->jenis : ($parts[2] ?? '02');
                $model->objek = filled($model->objek) ? $model->objek : ($parts[3] ?? '01');
                $model->rincian_objek = filled($model->rincian_objek) ? $model->rincian_objek : ($parts[4] ?? '001');
                $model->sub_rincian_objek = filled($model->sub_rincian_objek) ? $model->sub_rincian_objek : ($parts[5] ?? '00001');
                $model->sub_sub_rincian_objek = filled($model->sub_sub_rincian_objek) ? $model->sub_sub_rincian_objek : ($parts[6] ?? '000');
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

    /**
     * Dapatkan rincian hierarki berjenjang kode rekening sesuai standar SIPD RI
     * (Akun -> Kelompok -> Jenis -> Objek -> Rincian Objek -> Sub Rincian)
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
            '3' => '5.3 - BELANJA TAK TERDUGA',
            '4' => '5.4 - BELANJA TRANSFER',
            default => "{$kelompokKode} - Kelompok Belanja",
        };

        // 3. Jenis (5.1.02, dsb)
        $jenisStr = '-';
        if (isset($parts[2])) {
            $prefixJenis = $parts[0] . '.' . $parts[1] . '.' . $parts[2];
            $parent = self::where('kode_lengkap', $prefixJenis)->first();
            $jenisStr = $parent ? "{$parent->kode_lengkap} - {$parent->uraian}" : "{$prefixJenis} - Jenis Belanja";
        }

        // 4. Objek (5.1.02.01, dsb)
        $objekStr = '-';
        if (isset($parts[3])) {
            $prefixObjek = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.' . $parts[3];
            $parent = self::where('kode_lengkap', $prefixObjek)->first();
            $objekStr = $parent ? "{$parent->kode_lengkap} - {$parent->uraian}" : "{$prefixObjek} - Objek Belanja";
        }

        // 5. Rincian Objek (5.1.02.01.01 atau 5.1.02.01.001)
        $rincianStr = '-';
        if (isset($parts[4])) {
            $p4 = $parts[4];
            $candidates = [
                $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.' . $parts[3] . '.' . $p4,
                $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.' . $parts[3] . '.' . str_pad(ltrim($p4, '0'), 2, '0', STR_PAD_LEFT),
                $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.' . $parts[3] . '.' . str_pad(ltrim($p4, '0'), 3, '0', STR_PAD_LEFT),
            ];
            $parent = self::whereIn('kode_lengkap', $candidates)->first();
            $rincianStr = $parent ? "{$parent->kode_lengkap} - {$parent->uraian}" : "{$parts[0]}.{$parts[1]}.{$parts[2]}.{$parts[3]}.{$p4} - Rincian Objek Belanja";
        }

        return [
            'akun' => $akunStr,
            'kelompok' => $kelompokStr,
            'jenis' => $jenisStr,
            'objek' => $objekStr,
            'rincian_objek' => $rincianStr,
            'sub_rincian' => "{$this->kode_lengkap} - {$this->uraian}",
        ];
    }
}

