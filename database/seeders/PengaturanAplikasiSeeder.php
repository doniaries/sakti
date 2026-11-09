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
                'kode_instansi' => '3.26.2.19.0.00.01.0000',
                'nama_dinas' => 'DINAS PARIWISATA, PEMUDA DAN OLAH RAGA',
                'sub_unit_organisasi' => '-',
                'kepala_id' => $kadisUser?->id,
                'nama_kepala_dinas' => $kadisUser?->name ?? 'AFRINELDI, SH',
                'alamat_kantor' => 'Gedung Bersama Lt. III, Jl. Prof. M. Yamin, SH No. 1, Muaro Sijunjung, Sumatera Barat',
                'latitude_kantor' => -0.69752300,
                'longitude_kantor' => 100.98547200,
                'radius_meter' => 50,
                'logo' => null,
                'versi_aplikasi' => 'v1.0.0',
                'tahun_anggaran_aktif' => 2026,
                'pembuat_aplikasi' => 'Don Borland',
                'kontak_bantuan' => 'helpdesk@sakti.sijunjung.cloud',
                'deskripsi' => 'Sistem Administrasi, Kinerja, dan Tata Informasi',
            ]
        );
    }
}
