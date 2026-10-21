<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalKerja extends Model
{
    use HasFactory;

    protected $table = 'jadwal_kerjas';

    protected $fillable = [
        'nama',
        'kode',
        'jam_masuk_senin_kamis',
        'jam_pulang_senin_kamis',
        'jam_masuk_jumat',
        'jam_pulang_jumat',
        'toleransi_terlambat_menit',
        'is_default',
        'is_aktif',
        'keterangan',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_aktif' => 'boolean',
        'toleransi_terlambat_menit' => 'integer',
    ];

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->where('is_aktif', true)->first()
            ?? static::where('is_aktif', true)->first();
    }

    /**
     * Dapatkan jam masuk yang berlaku untuk tanggal tertentu
     */
    public function getJamMasukHari(Carbon|string $tanggal): string
    {
        $date = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);
        // 5 = Jumat di Carbon (dayOfWeekIso: 1=Mon, ..., 5=Fri)
        if ($date->dayOfWeekIso === 5) {
            return substr($this->jam_masuk_jumat, 0, 5);
        }

        return substr($this->jam_masuk_senin_kamis, 0, 5);
    }

    /**
     * Dapatkan jam pulang yang berlaku untuk tanggal tertentu
     */
    public function getJamPulangHari(Carbon|string $tanggal): string
    {
        $date = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);
        if ($date->dayOfWeekIso === 5) {
            return substr($this->jam_pulang_jumat, 0, 5);
        }

        return substr($this->jam_pulang_senin_kamis, 0, 5);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function absensiHarians(): HasMany
    {
        return $this->hasMany(AbsensiHarian::class);
    }
}
