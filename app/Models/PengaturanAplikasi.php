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
        'logo',
        'versi_aplikasi',
        'pembuat_aplikasi',
        'kontak_bantuan',
        'deskripsi',
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
                'versi_aplikasi' => 'v1.0.0',
                'pembuat_aplikasi' => 'Diskominfotik',
                'kontak_bantuan' => 'helpdesk@lhk.go.id',
                'deskripsi' => 'Sistem Presensi & Laporan Harian Kinerja Pegawai',
            ]);
        }

        return $pengaturan;
    }
}
