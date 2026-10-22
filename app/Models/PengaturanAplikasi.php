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
        'latitude_kantor',
        'longitude_kantor',
        'radius_meter',
        'alamat_kantor',
        'logo',
        'versi_aplikasi',
        'pembuat_aplikasi',
        'kontak_bantuan',
        'deskripsi',
    ];

    protected $casts = [
        'latitude_kantor' => 'float',
        'longitude_kantor' => 'float',
        'radius_meter' => 'integer',
    ];

    /**
     * Singleton helper untuk mendapatkan pengaturan aplikasi aktif
     */
    public static function getPengaturan(): self
    {
        /** @var self $pengaturan */
        $pengaturan = static::query()->first();

        if (! $pengaturan) {
            $pengaturan = static::query()->create([
                'nama_aplikasi' => 'ABSENSI LHK',
                'sub_nama' => 'Dinas Lingkungan Hidup',
                'latitude_kantor' => -0.69752300,
                'longitude_kantor' => 100.98547200,
                'radius_meter' => 150,
                'alamat_kantor' => 'Muaro Sijunjung, Sumatera Barat',
                'versi_aplikasi' => 'v1.0.0',
                'pembuat_aplikasi' => 'Diskominfotik',
                'kontak_bantuan' => 'helpdesk@lhk.go.id',
                'deskripsi' => 'Sistem Presensi & Laporan Harian Kinerja Pegawai',
            ]);
        }

        return $pengaturan;
    }
}
