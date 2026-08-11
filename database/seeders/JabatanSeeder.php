<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jabatan;
use Illuminate\Support\Str;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        $jabatans = [
            'Adyatama Kepariwisataan dan Ekonomi Kreatif Ahli Pertama',
            'Ahli Pertama-Analis Kebijakan',
            'Ahli Pertama-Perencana',
            'Analis Kebijakan Ahli Muda',
            'Analis Kebijakan Ahli Pertama',
            'Asisten Pelatih Olahraga Pemula',
            'KABID PEMASARAN PARIWISATA',
            'KABID PEMUDA DAN OLAHRAGA',
            'KABID PPENGEMBANGAN DESTINASI PARIWISATA',
            'KEPALA DINAS',
            'Kasubbag Umum dan Kepegawaian',
            'Konservator',
            'Operator Layanan Operasional',
            'Pelatih Olahraga Ahli Pertama',
            'Penata Kelola Sistem dan Teknologi',
            'Penata Layanan Operasional',
            'Penelaah Teknis Kebijakan',
            'Penerjemah Ahli Pertama',
            'Pengadministrasi Perkantoran',
            'Pengelola Layanan Operasional',
            'Pengelola Umum Operasional',
            'Pranata Hubungan Msyarakat Ahli Pertama',
            'Pranata Komputer Ahli Pertama',
            'SEKRETARIS',
        ];

        foreach ($jabatans as $nama) {
            Jabatan::firstOrCreate([
                'nama' => $nama,
                'slug' => Str::slug($nama)
            ]);
        }
    }
}
