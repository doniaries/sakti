<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Instansi;

class InstansiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Instansi::updateOrCreate(
            ['id' => 1],
            [
                'nama_dinas' => 'Dinas Pariwisata Pemuda dan Olahraga',
                'nama_kepala_dinas' => 'AFRINELDI, SH',
                'alamat' => 'Gedung Bersama Lt. III',
                'logo' => null, // akan menggunakan default jika logo belum diupload (bisa diatur di view/widget)
            ]
        );
    }
}
