<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LhkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\RencanaAksi::create([
            'rencana_aksi' => 'Diluar Sasaran Kinerja'
        ]);
        
        \App\Models\RencanaAksi::create([
            'rencana_aksi' => 'Melaksanakan Tugas Lain yang diberikan Pimpinan'
        ]);

        $aktivitas = [
            ['kode' => '1010', 'nama_aktivitas' => 'Mengikuti Apel Pagi', 'menit' => 30],
            ['kode' => '8445626886', 'nama_aktivitas' => 'Mengikuti sosialisasi/bimtek/rapat/pelatihan/diklat secara daring', 'menit' => 180],
            ['kode' => '1018', 'nama_aktivitas' => 'Memimpin rapat/FGD/kegiatan/sosialisasi/bimtek/pelatihan/workshop', 'menit' => 180],
            ['kode' => '1214476978', 'nama_aktivitas' => 'Mengarahkan tim kreatif dalam pengembangan ide kampanye pariwisata daerah', 'menit' => 45],
            ['kode' => '1294127126', 'nama_aktivitas' => 'Melakukan persetujuan akhir (ACC) produk kreatif', 'menit' => 30],
        ];

        foreach ($aktivitas as $akt) {
            \App\Models\Aktivitas::create($akt);
        }
    }
}
