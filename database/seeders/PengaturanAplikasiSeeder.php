<?php

namespace Database\Seeders;

use App\Models\PengaturanAplikasi;
use Illuminate\Database\Seeder;

class PengaturanAplikasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kadisUser = \App\Models\User::where('name', 'like', '%AFRINELDI%')->first()
            ?? \App\Models\User::whereNull('bidang_id')->first()
            ?? \App\Models\User::first();

        PengaturanAplikasi::updateOrCreate(
            ['id' => 1],
            [
                'nama_aplikasi' => 'Sakti',
                'sub_nama' => 'Sistem Administrasi, Kinerja, dan Tata Informasi',
                'nama_dinas' => 'Dinas Pariwisata Pemuda dan Olahraga',
                'kepala_id' => $kadisUser?->id,
                'nama_kepala_dinas' => $kadisUser?->name ?? 'AFRINELDI, SH',
                'alamat_kantor' => 'Gedung Bersama Lt. III, Jl. Prof. M. Yamin, SH No. 1, Muaro Sijunjung, Sumatera Barat',
                'latitude_kantor' => -0.69752300,
                'longitude_kantor' => 100.98547200,
                'radius_meter' => 50,
                'logo' => null,
                'versi_aplikasi' => 'v1.0.0',
                'pembuat_aplikasi' => 'Don Borland',
                'kontak_bantuan' => 'helpdesk@lhk.sijunjung.go.id',
                'deskripsi' => 'Sistem Administrasi, Kinerja, dan Tata Informasi',
            ]
        );
    }
}
