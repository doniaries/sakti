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
            'app_version' => 'v1.2.0',
            'update_notes' => '1. Notifikasi Web Push (VAPID) Android & geser hapus notif. 2. Peningkatan akurasi GPS (watchPosition) & preset lokasi simulasi. 3. Edit/hapus Surat Tugas, pilihan pejabat penandatangan, & cek kelengkapan pegawai via WhatsApp. 4. Desain baru halaman login PWA & menu Pengaturan Aplikasi.',
        ]);
    }
}
