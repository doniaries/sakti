<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PengaturanAplikasi;
use Illuminate\Database\Seeder;

class UpdateAplikasiSeeder extends Seeder
{
    public function run(): void
    {
        $pengaturan = PengaturanAplikasi::getPengaturan();

        $pengaturan->update([
            'app_version' => 'v1.3.0',
            'update_notes' => "1. Notifikasi Android FCM real-time (ikon SAKTI, suara, getar, bangunkan layar, & klik langsung ke halaman).\n2. Login cepat Google & Sidik Jari di Android + status izin GPS/Notifikasi.\n3. Preview & ganti foto profil di Dashboard & Profil, tombol Perbarui GPS lebih mudah dijangkau, serta pull-to-refresh.\n4. Filter, pengingat, & cetak PDF Pegawai Belum Lengkap di Admin, serta Mode Simulasi khusus Superadmin.",
        ]);
    }
}
