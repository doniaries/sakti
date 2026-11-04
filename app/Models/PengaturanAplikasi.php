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
        'kepala_id',
        'nama_kepala_dinas',
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

    public function kepala(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'kepala_id');
    }

    protected static function booted(): void
    {
        static::saved(function (self $pengaturan) {
            // Sinkronisasi data ke model Instansi demi kompatibilitas penuh
            try {
                \App\Models\Instansi::updateOrCreate(
                    ['id' => 1],
                    [
                        'nama_dinas' => $pengaturan->nama_dinas ?: $pengaturan->sub_nama,
                        'nama_kepala_dinas' => $pengaturan->nama_kepala_dinas,
                        'alamat' => $pengaturan->alamat_kantor,
                        'logo' => $pengaturan->logo,
                        'kepala_id' => $pengaturan->kepala_id,
                    ]
                );
            } catch (\Throwable $e) {
                // Abaikan jika tabel instansis belum siap
            }
        });
    }

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
                'nama_aplikasi' => 'Sakti',
                'sub_nama' => 'Sistem Administrasi, Kinerja, dan Tata Informasi',
                'nama_dinas' => 'Dinas Pariwisata Pemuda dan Olahraga',
                'latitude_kantor' => -0.69752300,
                'longitude_kantor' => 100.98547200,
                'radius_meter' => 50,
                'alamat_kantor' => 'Gedung Bersama Lt. III, Jl. Prof. M. Yamin, SH No. 1, Muaro Sijunjung, Sumatera Barat',
                'versi_aplikasi' => 'v1.0.0',
                'pembuat_aplikasi' => 'Don Borland',
                'kontak_bantuan' => 'helpdesk@lhk.sijunjung.go.id',
                'deskripsi' => 'Sistem Administrasi, Kinerja, dan Tata Informasi',
            ]);
        }

        return $pengaturan;
    }
}
