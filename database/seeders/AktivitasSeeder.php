<?php

namespace Database\Seeders;

use App\Models\Aktivitas;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AktivitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define users based on email
        $emails = [
            'kepaladinas@gmail.com', // Kadis (Afrineldi)
            'sekretaris@gmail.com',  // Sekretaris (Desmiwarti)
            'admin@gmail.com',       // Kasubag Umum (Zulfitri)
            'kabid@gmail.com',       // Kabid (Indrawadi)
            'wilda@gmail.com',       // Pegawai (Wilda)
            'doniaries@gmail.com',   // doni aries (existing)
        ];

        $users = User::whereIn('email', $emails)->get();

        $aktivitasList = [
            ['nama_aktivitas' => 'Mengikuti Apel Pagi', 'menit' => 30],
            ['nama_aktivitas' => 'Mengikuti sosialisasi/bimtek/rapat/pelatihan/diklat secara daring (zoom/google meet)', 'menit' => 180],
            ['nama_aktivitas' => 'Memimpin rapat/FGD/kegiatan/sosialisasi/bimtek/pelatihan/workshop', 'menit' => 180],
            ['nama_aktivitas' => 'Mengarahkan tim kreatif dalam pengembangan ide kampanye pariwisata daerah dan memvalidasi rilis berita.', 'menit' => 45],
            ['nama_aktivitas' => 'Melakukan persetujuan akhir (ACC) produk kreatif (video/desain) sebelum tayang ke publik.', 'menit' => 30],
            ['nama_aktivitas' => 'Pelaksanaan Promosi Pariwisata', 'menit' => 300],
            ['nama_aktivitas' => 'Memfasilitasi dalam pengembangan pelaku pariwisata', 'menit' => 300],
            ['nama_aktivitas' => 'Menghadiri Musrenbang Perubahan RPJM', 'menit' => 300],
            ['nama_aktivitas' => 'Melakukan koordinasi dan konsultasi dengan Kab/Kota/Provinsi Lain', 'menit' => 300],
            ['nama_aktivitas' => 'Terselnggaranya Pelaksanaan Penerjemahan untuk Mempromosikan Kepariwisataan', 'menit' => 300],
            ['nama_aktivitas' => 'Mengikuti sosialiasi/bimtek/rapat/pelantikan/diklat secara daring (zoom)', 'menit' => 300],
            ['nama_aktivitas' => 'Melakukan peninjauan dan monitoring langsung ke lapangan pada saat pelaksanaan promosi pariwisata, pameran ekraf, atau penyelenggaraan event', 'menit' => 240],
            ['nama_aktivitas' => 'Monitoring dan Evaluasi Kawasan Strategis dan Kawasan Pengembangan Pariwisata', 'menit' => 300],
            ['nama_aktivitas' => 'Lanjutan menghadiri rapat/FGD/kegiatan/sosialisasi/bimtek/pelatihan/workshop', 'menit' => 60],
            ['nama_aktivitas' => 'Mengevaluasi laporan statistik jangkauan media sosial dan data kunjungan wisatawan secara berkala.', 'menit' => 45],
            ['nama_aktivitas' => 'Menghadiri lanjutan sosialisasi', 'menit' => 60],
            ['nama_aktivitas' => 'Pengembangan Pemasaran Ekonomi Kreatif', 'menit' => 120],
            ['nama_aktivitas' => 'Mengoordinasikan sinkronisasi materi promosi dengan pihak eksternal atau lintas bidang.', 'menit' => 60],
            ['nama_aktivitas' => 'Melakukan koordinasi dengan perangkat daerah terkait program kerja kegiatan pemerintah daerah', 'menit' => 240],
            ['nama_aktivitas' => 'Menghadiri kegiatan massal/ seremoni di lingkungan Pemerintah Daerah', 'menit' => 120],
            ['nama_aktivitas' => 'Melakukan koordinasi dan konsultasi dengan perangkat daerah', 'menit' => 180],
            ['nama_aktivitas' => 'Melakukan koordinasi di lingkup Internal', 'menit' => 120],
            ['nama_aktivitas' => 'Menjadi narasumber pada sosialisasi/bimtek/workshop/kegiatan/rapat/pelatihan/diklat)', 'menit' => 180],
            ['nama_aktivitas' => 'melakukan kegiatan rembuk naskah (script conference) untuk produksi video', 'menit' => 180],
            ['nama_aktivitas' => 'Menghadiri undangan dari Nagari, Instansi,sekolah, lembaga keagamaan dan Rumah Ibadah', 'menit' => 120],
            ['nama_aktivitas' => 'Mereview draf naskah edukasi/informasi panduan wisata untuk pengunjung di lokasi objek wisata.', 'menit' => 30],
            ['nama_aktivitas' => 'Menghadiri sosialisasi dikabupaten', 'menit' => 90],
            ['nama_aktivitas' => 'Membuat, menganalisa, menyusun narasi, mendesain dan mengunggah info grafis terkait data kunjungan wisatawan ke Sumatera Barat melalui media sosial.', 'menit' => 300],
        ];

        // Global activity code counter starting from 1
        $kodeCounter = 1;

        foreach ($users as $user) {
            foreach ($aktivitasList as $item) {
                Aktivitas::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'nama_aktivitas' => $item['nama_aktivitas'],
                    ],
                    [
                        'kode' => (string) $kodeCounter,
                        'menit' => $item['menit'],
                    ]
                );
                
                $kodeCounter++;
            }
        }
    }
}
