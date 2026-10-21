<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 */
class HariLibur extends Model
{
    use HasFactory;

    protected $table = 'hari_liburs';

    protected $fillable = [
        'tanggal',
        'nama',
        'jenis',
        'sumber',
        'keterangan',
        'is_aktif',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_aktif' => 'boolean',
    ];

    /**
     * Cek apakah suatu tanggal adalah hari libur (Sabtu, Minggu, atau terdaftar di hari_liburs)
     */
    public static function isHariLibur(Carbon|string $tanggal): bool
    {
        $date = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        // Jika akhir pekan (Sabtu / Minggu)
        if ($date->isWeekend()) {
            return true;
        }

        return static::whereDate('tanggal', $date->toDateString())
            ->where('is_aktif', true)
            ->exists();
    }

    /**
     * Dapatkan data hari libur jika ada
     */
    public static function getLibur(Carbon|string $tanggal): ?self
    {
        $date = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        return static::whereDate('tanggal', $date->toDateString())
            ->where('is_aktif', true)
            ->first();
    }
}
