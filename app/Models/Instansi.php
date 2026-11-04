<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instansi extends Model
{
    protected $fillable = ['nama_dinas', 'kode_instansi', 'sub_unit_organisasi', 'nama_kepala_dinas', 'alamat', 'logo', 'kepala_id'];

    public function kepala()
    {
        return $this->belongsTo(User::class, 'kepala_id');
    }

    protected static function booted(): void
    {
        static::saved(function (self $instansi) {
            try {
                $pengaturan = PengaturanAplikasi::first();
                if ($pengaturan) {
                    $pengaturan->updateQuietly([
                        'nama_dinas' => $instansi->nama_dinas,
                        'nama_kepala_dinas' => $instansi->nama_kepala_dinas,
                        'alamat_kantor' => $instansi->alamat,
                        'logo' => $instansi->logo ?? $pengaturan->logo,
                        'kepala_id' => $instansi->kepala_id ?? $pengaturan->kepala_id,
                    ]);
                }
            } catch (\Throwable $e) {
            }
        });
    }
}
