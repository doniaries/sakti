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
        $doni = User::where('name', 'like', '%doni aries%')->first();
        
        $data = [
            ['kode' => '1010', 'nama_aktivitas' => 'Mengikuti Apel Pagi', 'menit' => 30],
            ['kode' => '8445626886', 'nama_aktivitas' => 'Mengikuti sosialisasi/bimtek/rapat/pelatihan/diklat secara daring (zoom/google meet)', 'menit' => 180],
            ['kode' => '1018', 'nama_aktivitas' => 'Memimpin rapat/FGD/kegiatan/sosialisasi/bimtek/pelatihan/workshop', 'menit' => 180],
            ['kode' => '1214476978', 'nama_aktivitas' => 'Mengarahkan tim kreatif dalam pengembangan ide kampanye pariwisata daerah dan memvalidasi rilis berita.', 'menit' => 45],
            ['kode' => '1294127126', 'nama_aktivitas' => 'Melakukan persetujuan akhir (ACC) produk kreatif (video/desain) sebelum tayang ke publik.', 'menit' => 30],
            ['kode' => '1493383745', 'nama_aktivitas' => 'Pelaksanaan Promosi Pariwisata', 'menit' => 300],
            ['kode' => '2238684474', 'nama_aktivitas' => 'Memfasilitasi dalam pengembangan pelaku pariwisata', 'menit' => 300],
            ['kode' => '2281279933', 'nama_aktivitas' => 'Menghadiri Musrenbang Perubahan RPJM', 'menit' => 300],
            ['kode' => '2326131549', 'nama_aktivitas' => 'Melakukan koordinasi dan konsultasi dengan Kab/Kota/Provinsi Lain', 'menit' => 300],
            ['kode' => '2599336982', 'nama_aktivitas' => 'Terselnggaranya Pelaksanaan Penerjemahan untuk Mempromosikan Kepariwisataan', 'menit' => 300],
            ['kode' => '2992597623', 'nama_aktivitas' => 'Mengikuti sosialiasi/bimtek/rapat/pelantikan/diklat secara daring (zoom)', 'menit' => 300],
            ['kode' => '3958578873', 'nama_aktivitas' => 'Melakukan peninjauan dan monitoring langsung ke lapangan pada saat pelaksanaan promosi pariwisata, pameran ekraf, atau penyelenggaraan event', 'menit' => 240],
            ['kode' => '4447294783', 'nama_aktivitas' => 'Monitoring dan Evaluasi Kawasan Strategis dan Kawasan Pengembangan Pariwisata', 'menit' => 300],
            ['kode' => '4598136533', 'nama_aktivitas' => 'Lanjutan menghadiri rapat/FGD/kegiatan/sosialisasi/bimtek/pelatihan/workshop', 'menit' => 60],
            ['kode' => '5394692431', 'nama_aktivitas' => 'Mengevaluasi laporan statistik jangkauan media sosial dan data kunjungan wisatawan secara berkala.', 'menit' => 45],
            ['kode' => '5578875515', 'nama_aktivitas' => 'Menghadiri lanjutan sosialisasi', 'menit' => 60],
            ['kode' => '6143862966', 'nama_aktivitas' => 'Pengembangan Pemasaran Ekonomi Kreatif', 'menit' => 120],
            ['kode' => '6342212153', 'nama_aktivitas' => 'Mengoordinasikan sinkronisasi materi promosi dengan pihak eksternal atau lintas bidang.', 'menit' => 60],
            ['kode' => '6434579479', 'nama_aktivitas' => 'Melakukan koordinasi dengan perangkat daerah terkait program kerja kegiatan pemerintah daerah', 'menit' => 240],
            ['kode' => '6592234925', 'nama_aktivitas' => 'Menghadiri kegiatan massal/ seremoni di lingkungan Pemerintah Daerah', 'menit' => 120],
            ['kode' => '6671226362', 'nama_aktivitas' => 'Melakukan koordinasi dan konsultasi dengan perangkat daerah', 'menit' => 180],
            ['kode' => '8463764583', 'nama_aktivitas' => 'Melakukan koordinasi di lingkup Internal', 'menit' => 120],
            ['kode' => '8781314143', 'nama_aktivitas' => 'Menjadi narasumber pada sosialisasi/bimtek/workshop/kegiatan/rapat/pelatihan/diklat)', 'menit' => 180],
            ['kode' => '8964199589', 'nama_aktivitas' => 'melakukan kegiatan rembuk naskah (script conference) untuk produksi video', 'menit' => 180],
            ['kode' => '9264579876', 'nama_aktivitas' => 'Menghadiri undangan dari Nagari, Instansi,sekolah, lembaga keagamaan dan Rumah Ibadah', 'menit' => 120],
            ['kode' => '9668979227', 'nama_aktivitas' => 'Mereview draf naskah edukasi/informasi panduan wisata untuk pengunjung di lokasi objek wisata.', 'menit' => 30],
            ['kode' => '9697878543', 'nama_aktivitas' => 'Menghadiri sosialisasi dikabupaten', 'menit' => 90],
            ['kode' => '8617833076', 'nama_aktivitas' => 'Membuat, menganalisa, menyusun narasi, mendesain dan mengunggah info grafis terkait data kunjungan wisatawan ke Sumatera Barat melalui media sosial.', 'menit' => 300],
        ];

        foreach ($data as $item) {
            $item['user_id'] = $doni ? $doni->id : null;
            Aktivitas::updateOrCreate(
                ['kode' => $item['kode']],
                $item
            );
        }
    }
}
