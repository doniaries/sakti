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
        PengaturanAplikasi::updateOrCreate(
            ['id' => 1],
            [
                'nama_aplikasi' => 'absensilhk',
                'sub_nama' => 'Dinas Lingkungan Hidup',
                'logo' => null,
                'versi_aplikasi' => '1.0.1',
                'pembuat_aplikasi' => 'don borland',
                'kontak_bantuan' => 'helpdesk@lhk.go.id',
                'deskripsi' => 'Sistem Presensi & Laporan Harian Kinerja Pegawai (LHK)',
            ]
        );
    }
}
