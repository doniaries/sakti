<?php

namespace Database\Seeders;

use App\Models\Aktivitas;
use App\Models\Bidang;
use App\Models\RencanaAksi;
use App\Models\User;
use Illuminate\Database\Seeder;

class AktivitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Aktivitas Global (user_id & jabatan_id null -> otomatis tersedia untuk semua pegawai)
        $globalList = [
            ['nama_aktivitas' => 'Mengikuti Apel Pagi', 'menit' => 30],
            ['nama_aktivitas' => 'Melakukan koordinasi di lingkup internal bidang/dinas', 'menit' => 120],
            ['nama_aktivitas' => 'Melakukan koordinasi dan konsultasi dengan perangkat daerah', 'menit' => 180],
            ['nama_aktivitas' => 'Mengikuti sosialisasi/bimtek/rapat/pelatihan/diklat secara daring (zoom/google meet)', 'menit' => 180],
            ['nama_aktivitas' => 'Mengikuti sosialisasi/bimtek/rapat/pelatihan/diklat secara luring', 'menit' => 300],
            ['nama_aktivitas' => 'Menghadiri kegiatan massal/seremoni di lingkungan Pemerintah Daerah', 'menit' => 120],
            ['nama_aktivitas' => 'Menghadiri undangan dari Nagari, instansi, sekolah, dan lembaga terkait', 'menit' => 120],
            ['nama_aktivitas' => 'Lanjutan menghadiri rapat/FGD/kegiatan/sosialisasi/bimtek/pelatihan/workshop', 'menit' => 60],
        ];

        foreach ($globalList as $item) {
            Aktivitas::updateOrCreate(
                [
                    'user_id' => null,
                    'jabatan_id' => null,
                    'nama_aktivitas' => $item['nama_aktivitas'],
                ],
                [
                    'bidang_id' => null,
                    'menit' => $item['menit'],
                    'status' => 'disetujui',
                ]
            );
        }

        // 2. Aktivitas Bidang Pariwisata (disebar ke pegawai Bidang Pariwisata sesuai jabatan)
        $bidangPariwisata = Bidang::where('nama', 'like', '%Pariwisata%')->first();
        if (! $bidangPariwisata) {
            return;
        }

        $bankAktivitasKabid = [
            ['nama_aktivitas' => 'Memimpin rapat/FGD/sosialisasi/bimtek/workshop bidang pariwisata', 'menit' => 180, 'sub_kegiatan' => 'Umum'],
            ['nama_aktivitas' => 'Memimpin rapat persiapan dan evaluasi pelaksanaan sub kegiatan bidang', 'menit' => 120, 'sub_kegiatan' => 'Umum'],
            ['nama_aktivitas' => 'Melakukan koordinasi dengan perangkat daerah terkait program destinasi pariwisata', 'menit' => 180, 'sub_kegiatan' => 'Perencanaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Melakukan koordinasi dan konsultasi dengan Kab/Kota/Provinsi lain terkait pengembangan destinasi', 'menit' => 300, 'sub_kegiatan' => 'Pengembangan Destinasi Pariwisata Kabupaten/Kota Sesuai dengan Tahapan (Rintisan, Berkembang, Pemantapan, Revitalisasi)'],
            ['nama_aktivitas' => 'Menjadi narasumber pada sosialisasi/bimtek/workshop/pelatihan pengelola destinasi', 'menit' => 180, 'sub_kegiatan' => 'Peningkatan Kapasitas SDM Pengelola Kawasan Strategis Pariwisata'],
            ['nama_aktivitas' => 'Menerima kunjungan kerja/studi banding terkait pengelolaan destinasi pariwisata', 'menit' => 120, 'sub_kegiatan' => 'Pengembangan Destinasi Pariwisata Kabupaten/Kota Sesuai dengan Tahapan (Rintisan, Berkembang, Pemantapan, Revitalisasi)'],
            ['nama_aktivitas' => 'Melakukan peninjauan dan monitoring langsung ke lapangan pada kawasan strategis/destinasi pariwisata', 'menit' => 240, 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Monitoring dan Evaluasi Kawasan Strategis dan Kawasan Pengembangan Pariwisata', 'menit' => 300, 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Memeriksa dan memvalidasi dokumen perencanaan kawasan strategis/destinasi pariwisata', 'menit' => 120, 'sub_kegiatan' => 'Perencanaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Memeriksa dan memvalidasi laporan hasil monitoring dan evaluasi destinasi', 'menit' => 60, 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Mengarahkan tim dalam pengembangan destinasi dan memvalidasi hasil kerja JF/staf', 'menit' => 45, 'sub_kegiatan' => 'Umum'],
            ['nama_aktivitas' => 'Melakukan persetujuan akhir (ACC) dokumen/hasil pekerjaan bidang sebelum diteruskan ke Kepala Dinas', 'menit' => 30, 'sub_kegiatan' => 'Umum'],
            ['nama_aktivitas' => 'Membuka dan menutup pelatihan/bimtek pengelola kawasan strategis dan destinasi pariwisata', 'menit' => 60, 'sub_kegiatan' => 'Peningkatan Kapasitas SDM Pengelola Kawasan Strategis Pariwisata'],
            ['nama_aktivitas' => 'Memaparkan program dan capaian pengembangan destinasi kepada pimpinan/Bappeda', 'menit' => 120, 'sub_kegiatan' => 'Perencanaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Mengevaluasi capaian kinerja program dan laporan bidang secara berkala', 'menit' => 60, 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
        ];

        $bankAktivitasJF = [
            ['nama_aktivitas' => 'Melakukan survei dan pemetaan potensi kawasan strategis/destinasi pariwisata', 'menit' => 300, 'sub_kegiatan' => 'Perencanaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Melakukan verifikasi lapangan calon kawasan strategis pariwisata', 'menit' => 300, 'sub_kegiatan' => 'Penetapan Kawasan Strategis Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Menyusun dokumen perencanaan kawasan strategis pariwisata', 'menit' => 300, 'sub_kegiatan' => 'Perencanaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Menyusun dokumen perencanaan destinasi pariwisata', 'menit' => 300, 'sub_kegiatan' => 'Perencanaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Menyusun kajian teknis penetapan kawasan strategis pariwisata', 'menit' => 240, 'sub_kegiatan' => 'Penetapan Kawasan Strategis Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Menyusun konsep keputusan/dokumen penetapan kawasan strategis pariwisata', 'menit' => 180, 'sub_kegiatan' => 'Penetapan Kawasan Strategis Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Menganalisis data hasil survei kawasan strategis/destinasi pariwisata', 'menit' => 120, 'sub_kegiatan' => 'Perencanaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Melakukan inventarisasi (opname) sarana dan prasarana kawasan strategis/destinasi', 'menit' => 300, 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Menyusun rencana kebutuhan dan spesifikasi teknis sarana prasarana destinasi', 'menit' => 180, 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Melakukan pengawasan pekerjaan sarana dan prasarana di lapangan', 'menit' => 240, 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Melakukan pemeriksaan hasil pekerjaan sarana dan prasarana destinasi', 'menit' => 180, 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Melakukan pendampingan penerapan destinasi pariwisata berkelanjutan di lokasi', 'menit' => 300, 'sub_kegiatan' => 'Penerapan Destinasi Pariwisata Berkelanjutan dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Melakukan penilaian penerapan kriteria destinasi pariwisata berkelanjutan', 'menit' => 180, 'sub_kegiatan' => 'Penerapan Destinasi Pariwisata Berkelanjutan dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Melakukan penilaian tahapan pengembangan destinasi (rintisan, berkembang, pemantapan, revitalisasi)', 'menit' => 240, 'sub_kegiatan' => 'Pengembangan Destinasi Pariwisata Kabupaten/Kota Sesuai dengan Tahapan (Rintisan, Berkembang, Pemantapan, Revitalisasi)'],
            ['nama_aktivitas' => 'Melakukan pendampingan pengembangan destinasi kepada pengelola dan Pokdarwis', 'menit' => 240, 'sub_kegiatan' => 'Pengembangan Destinasi Pariwisata Kabupaten/Kota Sesuai dengan Tahapan (Rintisan, Berkembang, Pemantapan, Revitalisasi)'],
            ['nama_aktivitas' => 'Memfasilitasi pemenuhan kriteria One Village One Destination', 'menit' => 240, 'sub_kegiatan' => 'Pengembangan Destinasi Pariwisata Kabupaten/Kota Sesuai dengan Tahapan (Rintisan, Berkembang, Pemantapan, Revitalisasi)'],
            ['nama_aktivitas' => 'Melakukan pelatihan dan pembinaan Pokdarwis/kelompok masyarakat pengelola destinasi', 'menit' => 300, 'sub_kegiatan' => 'Pemberdayaan Masyarakat dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Melakukan pendampingan kelompok masyarakat dalam pengelolaan destinasi', 'menit' => 240, 'sub_kegiatan' => 'Pemberdayaan Masyarakat dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Menyusun materi dan menjadi fasilitator pelatihan pengelola kawasan/destinasi', 'menit' => 300, 'sub_kegiatan' => 'Peningkatan Kapasitas SDM Pengelola Kawasan Strategis Pariwisata'],
            ['nama_aktivitas' => 'Melakukan pendampingan pasca pelatihan kepada pengelola destinasi', 'menit' => 240, 'sub_kegiatan' => 'Peningkatan Kapasitas SDM Pengelola Kawasan Strategis Pariwisata'],
            ['nama_aktivitas' => 'Menyusun instrumen monitoring dan evaluasi pengelolaan destinasi', 'menit' => 120, 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Melaksanakan monitoring dan evaluasi kawasan strategis/destinasi di lapangan', 'menit' => 300, 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Menyusun laporan hasil monitoring dan evaluasi pengelolaan destinasi', 'menit' => 240, 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Menyusun bahan paparan dan laporan capaian kinerja sub kegiatan', 'menit' => 120, 'sub_kegiatan' => 'Umum'],
            ['nama_aktivitas' => 'Memperbarui data destinasi pada SI DEWI MANDE', 'menit' => 120, 'sub_kegiatan' => 'Pengembangan Destinasi Pariwisata Kabupaten/Kota Sesuai dengan Tahapan (Rintisan, Berkembang, Pemantapan, Revitalisasi)'],
            ['nama_aktivitas' => 'Menghadiri rapat/FGD pembahasan dokumen dan program bidang', 'menit' => 180, 'sub_kegiatan' => 'Umum'],
            ['nama_aktivitas' => 'Melakukan koordinasi teknis dengan pemerintah nagari dan pengelola destinasi', 'menit' => 120, 'sub_kegiatan' => 'Umum'],
        ];

        $bankAktivitasStaf = [
            ['nama_aktivitas' => 'Menyiapkan administrasi kegiatan (surat tugas, undangan, daftar hadir)', 'menit' => 60, 'sub_kegiatan' => 'Umum'],
            ['nama_aktivitas' => 'Mengagendakan surat masuk dan surat keluar bidang', 'menit' => 30, 'sub_kegiatan' => 'Umum'],
            ['nama_aktivitas' => 'Menata dan mengarsipkan dokumen bidang', 'menit' => 60, 'sub_kegiatan' => 'Umum'],
            ['nama_aktivitas' => 'Membantu pengetikan dan penggandaan dokumen bidang', 'menit' => 60, 'sub_kegiatan' => 'Umum'],
            ['nama_aktivitas' => 'Menyiapkan berita acara kegiatan/serah terima sarana prasarana', 'menit' => 60, 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Menghubungi pemerintah nagari/pengelola untuk konfirmasi jadwal kegiatan', 'menit' => 30, 'sub_kegiatan' => 'Umum'],
            ['nama_aktivitas' => 'Mencatat hasil rapat (notulen) dan mengumpulkan daftar hadir', 'menit' => 120, 'sub_kegiatan' => 'Umum'],
            ['nama_aktivitas' => 'Menyiapkan instrumen survei/monitoring dan peralatan dokumentasi', 'menit' => 60, 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Menginput data hasil survei/pemetaan destinasi', 'menit' => 120, 'sub_kegiatan' => 'Perencanaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Menginput data destinasi ke SI DEWI MANDE', 'menit' => 120, 'sub_kegiatan' => 'Pengembangan Destinasi Pariwisata Kabupaten/Kota Sesuai dengan Tahapan (Rintisan, Berkembang, Pemantapan, Revitalisasi)'],
            ['nama_aktivitas' => 'Mendokumentasikan kondisi sarana prasarana (foto sebelum, progres, sesudah)', 'menit' => 120, 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Menginput dan merekap data hasil monitoring dan evaluasi', 'menit' => 120, 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Menginput realisasi kegiatan dan capaian kinerja bidang', 'menit' => 90, 'sub_kegiatan' => 'Umum'],
            ['nama_aktivitas' => 'Melayani permintaan data dan informasi destinasi pariwisata', 'menit' => 60, 'sub_kegiatan' => 'Umum'],
            ['nama_aktivitas' => 'Membantu pelaksanaan survei dan pemetaan di lapangan', 'menit' => 300, 'sub_kegiatan' => 'Perencanaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Membantu opname/inventarisasi sarana prasarana di lapangan', 'menit' => 300, 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Membantu pengisian instrumen monitoring di lapangan', 'menit' => 300, 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Mendata kelompok masyarakat/Pokdarwis di destinasi', 'menit' => 120, 'sub_kegiatan' => 'Pemberdayaan Masyarakat dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Membantu pelaksanaan pelatihan/pembinaan masyarakat di lapangan', 'menit' => 300, 'sub_kegiatan' => 'Pemberdayaan Masyarakat dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
            ['nama_aktivitas' => 'Menyiapkan undangan, daftar hadir, dan sertifikat pelatihan', 'menit' => 90, 'sub_kegiatan' => 'Peningkatan Kapasitas SDM Pengelola Kawasan Strategis Pariwisata'],
        ];

        $rencanaAksiByLevelSub = RencanaAksi::query()
            ->where('bidang_id', $bidangPariwisata->id)
            ->get(['id', 'level', 'sasaran_sub_kegiatan'])
            ->groupBy(fn (RencanaAksi $ra): string => ($ra->level ?? '') . '|' . ($ra->sasaran_sub_kegiatan ?? ''));

        $pegawaiPariwisata = User::with('jabatan:id,nama')
            ->where('aktif', true)
            ->where('bidang_id', $bidangPariwisata->id)
            ->get();

        foreach ($pegawaiPariwisata as $user) {
            $jabatanNama = strtolower((string) ($user->jabatan?->nama ?? ''));
            $level = match (true) {
                str_contains($jabatanNama, 'kepala bidang') || str_contains($jabatanNama, 'kabid') => 'kabid',
                str_contains($jabatanNama, 'fungsional') || str_contains($jabatanNama, 'analis') || str_contains($jabatanNama, 'adya') || str_contains($jabatanNama, 'muda') || str_contains($jabatanNama, 'pertama') => 'jf',
                default => 'staf',
            };
            $list = match ($level) {
                'kabid' => $bankAktivitasKabid,
                'jf' => $bankAktivitasJF,
                default => $bankAktivitasStaf,
            };

            foreach ($list as $item) {
                $raKey = $level . '|' . ($item['sub_kegiatan'] ?? 'Umum');
                $rencanaAksiId = $rencanaAksiByLevelSub->get($raKey)?->first()?->id;

                Aktivitas::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'nama_aktivitas' => $item['nama_aktivitas'],
                    ],
                    [
                        'jabatan_id' => $user->jabatan_id,
                        'bidang_id' => $bidangPariwisata->id,
                        'rencana_aksi_id' => $rencanaAksiId,
                        'menit' => $item['menit'],
                        'status' => 'disetujui',
                    ]
                );
            }
        }
    }
}
