<?php

namespace Database\Seeders;

use App\Enums\JabatanTimKreatif;
use App\Models\Aktivitas;
use App\Models\RencanaAksi;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder TUGAS TAMBAHAN — TIM KREATIF PUBLIKASI DAN PROMOSI DISPARPORA 2026.
 *
 * Sumber: Keputusan Kepala Dinas Pariwisata Pemuda dan Olahraga Kabupaten
 * Sijunjung Nomor 188.45/28/KPTS-DISPARPORA/2026 (Perubahan Ketiga, ditetapkan
 * 30 Agustus 2026) tentang Pembentukan Tim Kreatif Publikasi dan Promosi
 * Disparpora Kabupaten Sijunjung Tahun 2026.
 */
class TugasTambahanTimKreatifSeeder extends Seeder
{
    public function run(): void
    {
        $tahun = 2026;
        $sasaranProgram = 'Tugas Tambahan — SK Tim Kreatif Publikasi dan Promosi Disparpora 2026';

        $data = [
            // (a) Pembina — Kepala Dinas
            'pembina' => [
                'level' => 'kabid',
                'roles' => [JabatanTimKreatif::PEMBINA->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pembina Tim Kreatif Publikasi dan Promosi Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Memberikan arahan kebijakan, visi, dan strategi pelaksanaan publikasi dan promosi dinas', 'menit' => 60],
                    ['nama_aktivitas' => 'Menetapkan arah komunikasi publik untuk membangun citra positif kelembagaan dinas', 'menit' => 60],
                    ['nama_aktivitas' => 'Melakukan evaluasi menyeluruh pelaksanaan publikasi dan promosi dinas', 'menit' => 90],
                    ['nama_aktivitas' => 'Turun langsung ke lokasi kegiatan untuk peninjauan dan supervisi publikasi pimpinan', 'menit' => 180],
                ],
            ],
            // (b) Pengarah — Sekretaris
            'pengarah' => [
                'level' => 'kabid',
                'roles' => [JabatanTimKreatif::PENGARAH->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pengarah Tim Kreatif Publikasi dan Promosi Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengoordinasikan dan mengharmonisasikan pelaksanaan tugas publikasi lintas bidang', 'menit' => 90],
                    ['nama_aktivitas' => 'Memfasilitasi dukungan administrasi dan anggaran tim kreatif', 'menit' => 90],
                    ['nama_aktivitas' => 'Menerima dan memeriksa laporan berkala dari Penanggung Jawab Kegiatan Bidang', 'menit' => 60],
                    ['nama_aktivitas' => 'Turun langsung ke lokasi kegiatan untuk peninjauan dan supervisi publikasi lintas bidang', 'menit' => 180],
                ],
            ],
            // (c) Penanggung Jawab Kegiatan Bidang — Kepala Bidang
            'penanggung_jawab_bidang' => [
                'level' => 'kabid',
                'roles' => [JabatanTimKreatif::PENANGGUNG_JAWAB_BIDANG->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Penanggung Jawab Kegiatan Bidang Tim Kreatif Publikasi dan Promosi Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Menyusun dan merumuskan rencana kegiatan promosi/publikasi sesuai program kerja bidang', 'menit' => 90],
                    ['nama_aktivitas' => 'Memberikan instruksi dan arahan teknis kegiatan kepada Koordinator Teknis sebagai dasar peliputan', 'menit' => 45],
                    ['nama_aktivitas' => 'Memastikan kegiatan bidang terdokumentasi dan terpublikasi optimal serta mengevaluasi laporan hasil publikasi', 'menit' => 60],
                    ['nama_aktivitas' => 'Turun langsung ke lokasi kegiatan untuk peninjauan, supervisi, dan penyelesaian kendala teknis publikasi bidang', 'menit' => 180],
                ],
            ],
            // (d) Koordinator Kegiatan — Jabatan Fungsional (Koordinator Teknis)
            'koordinator_kegiatan' => [
                'level' => 'jf',
                'roles' => [JabatanTimKreatif::KOORDINATOR_TIM_KREATIF->value, JabatanTimKreatif::KOORDINATOR_TEKNIS->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Koordinator Kegiatan (Koordinator Teknis) Tim Kreatif Publikasi dan Promosi Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Menjabarkan arahan Penanggung Jawab Kegiatan Bidang menjadi rencana kerja teknis publikasi dan dokumentasi', 'menit' => 90],
                    ['nama_aktivitas' => 'Menetapkan penugasan, membagi jadwal peliputan, dan memberi target penyelesaian kerja kepada tim operasional', 'menit' => 60],
                    ['nama_aktivitas' => 'Melakukan pemeriksaan dan quality control tahap akhir foto, video, desain grafis, dan naskah sebelum tayang', 'menit' => 60],
                    ['nama_aktivitas' => 'Menyelesaikan kendala operasional di lapangan dan menyampaikan laporan pelaksanaan kegiatan kepada Kabid', 'menit' => 120],
                ],
            ],
            // (e) Koordinator Website Dinas dan Website Wisata
            'koordinator_website' => [
                'level' => 'jf',
                'roles' => [JabatanTimKreatif::KOORDINATOR_WEBSITE->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Koordinator Website Dinas dan Website Wisata Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengelola dan bertanggung jawab atas pemeliharaan serta publikasi Website Dinas dan Website Wisata', 'menit' => 120],
                    ['nama_aktivitas' => 'Menyusun jadwal peliputan, membagi tugas harian, dan mengarahkan anggota tim website', 'menit' => 60],
                    ['nama_aktivitas' => 'Menghimpun, menyortir, dan melakukan pemeriksaan mutu awal bahan dokumentasi/naskah anggota tim', 'menit' => 60],
                    ['nama_aktivitas' => 'Menyerahkan hasil pekerjaan unit yang telah diverifikasi kepada Koordinator Teknis untuk persetujuan tayang', 'menit' => 30],
                ],
            ],
            // (f) Pelaksana Website Dinas dan Website Wisata
            'pelaksana_website' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::PELAKSANA_WEBSITE->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pelaksana Website Dinas dan Website Wisata Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengelola konten Website Dinas maupun Website Wisata secara harian', 'menit' => 90],
                    ['nama_aktivitas' => 'Mengumpulkan data dan bahan informasi kegiatan bidang untuk publikasi website', 'menit' => 90],
                    ['nama_aktivitas' => 'Menyusun naskah berita dan artikel kegiatan untuk website resmi dinas/wisata', 'menit' => 120],
                    ['nama_aktivitas' => 'Menginput, memformat, dan menayangkan materi publikasi kegiatan ke dalam website', 'menit' => 60],
                ],
            ],
            // (g.1) Koordinator Dokumentasi Kegiatan
            'koordinator_dokumentasi' => [
                'level' => 'jf',
                'roles' => [JabatanTimKreatif::KOORDINATOR_DOKUMENTASI->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Koordinator Dokumentasi Kegiatan Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengoordinasikan pelaksanaan pengambilan foto dan video dokumentasi lapangan untuk kegiatan dinas', 'menit' => 240],
                    ['nama_aktivitas' => 'Mengatur jadwal penugasan peliputan fotografer dan videografer di lapangan', 'menit' => 45],
                    ['nama_aktivitas' => 'Melakukan pemeriksaan mutu awal hasil foto/video sebelum diserahkan kepada Koordinator Kegiatan', 'menit' => 60],
                    ['nama_aktivitas' => 'Melaksanakan peliputan langsung dan penyelesaian kendala dokumentasi di lokasi kegiatan', 'menit' => 180],
                ],
            ],
            // (g.2) Koordinator Media Sosial dan Desain Grafis
            'koordinator_medsos_desain' => [
                'level' => 'jf',
                'roles' => [JabatanTimKreatif::KOORDINATOR_MEDIA_SOSIAL->value, JabatanTimKreatif::KOORDINATOR_DESAIN_GRAFIS->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Koordinator Media Sosial dan Desain Grafis Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengelola standar visual akun media sosial dan standar desain promosi resmi dinas', 'menit' => 90],
                    ['nama_aktivitas' => 'Mengarahkan pengelola media sosial dan desainer grafis dalam produksi konten harian', 'menit' => 60],
                    ['nama_aktivitas' => 'Melakukan pemeriksaan mutu awal karya desain grafis dan takarir sebelum tahap publikasi', 'menit' => 60],
                    ['nama_aktivitas' => 'Mengevaluasi performa visual dan keterjangkauan kampanye promosi di media sosial dinas', 'menit' => 60],
                ],
            ],
            // (h.1) Pengelola Media Sosial
            'pengelola_media_sosial' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::PENGELOLA_MEDIA_SOSIAL->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pengelola Media Sosial Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Merencanakan jadwal tayang (posting) dan mengelola akun media sosial resmi dinas dan pariwisata (Instagram, Facebook, TikTok)', 'menit' => 60],
                    ['nama_aktivitas' => 'Menyusun narasi atau takarir (caption) dan tagar publikasi yang informatif dan menarik', 'menit' => 60],
                    ['nama_aktivitas' => 'Mengunggah konten publikasi yang telah disetujui ke seluruh kanal media sosial dinas', 'menit' => 30],
                    ['nama_aktivitas' => 'Merespons pertanyaan/komentar pengikut serta memantau analitik kinerja akun media sosial', 'menit' => 60],
                ],
            ],
            // (h.2) Desain Grafis
            'desain_grafis' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::DESAIN_GRAFIS->value],
                'extra_names' => ['AGUSMEDI ARIF', 'ROSY WITRIN', 'FITRI AMELIA'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Desainer Grafis Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Merancang dan memproduksi materi visual promosi (infografis, poster, banner, dan spanduk kegiatan)', 'menit' => 180],
                    ['nama_aktivitas' => 'Mendesain konten grafis feed, carousel, dan story media sosial sesuai agenda dinas', 'menit' => 120],
                    ['nama_aktivitas' => 'Melakukan pengolahan warna (retouching), penyesuaian tata letak (layout), dan penyuntingan foto', 'menit' => 90],
                    ['nama_aktivitas' => 'Menyelaraskan seluruh karya desain dengan identitas visual dan standar publikasi dinas', 'menit' => 45],
                ],
            ],
            // (h.3a) Dokumentasi — Fotografer
            'fotografer' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::DOKUMENTASI->value],
                'extra_names' => ['JENI ZULMARLY KAMIL', 'ANDRA PEBTALIYUS', 'NAZHIRUL RIFQI ALNEV', 'PRADIUTAMA JULIYANSA', 'FARHANUL HUDA'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Fotografer Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Turun langsung ke lokasi kegiatan untuk dokumentasi foto seluruh rangkaian acara dinas', 'menit' => 240],
                    ['nama_aktivitas' => 'Mengumpulkan data awal bahan narasi/wawancara dan menyerahkan raw data kepada Koordinator Kegiatan', 'menit' => 60],
                    ['nama_aktivitas' => 'Melakukan seleksi, pengolahan warna, dan penyuntingan (editing) foto sesuai standar publikasi', 'menit' => 120],
                    ['nama_aktivitas' => 'Menyusun narasi singkat terkait kegiatan yang telah didokumentasikan', 'menit' => 45],
                ],
            ],
            // (h.3b) Dokumentasi — Videografer
            'videografer' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::DOKUMENTASI->value],
                'extra_names' => ['JENI ZULMARLY KAMIL', 'ANDRA PEBTALIYUS', 'NAZHIRUL RIFQI ALNEV', 'PRADIUTAMA JULIYANSA', 'FARHANUL HUDA'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Videografer Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Turun langsung ke lokasi kegiatan untuk merekam gambar bergerak (video) dan audio rangkaian acara', 'menit' => 240],
                    ['nama_aktivitas' => 'Mengumpulkan bahan tayangan pendukung seperti wawancara singkat (doorstop/testimoni) di lapangan', 'menit' => 90],
                    ['nama_aktivitas' => 'Melakukan penyuntingan (editing) video, transisi, dan penambahan audio/teks menjadi konten promosi atau reels', 'menit' => 240],
                    ['nama_aktivitas' => 'Membuat narasi singkat dan menyerahkan hasil video siap tayang kepada Koordinator Kegiatan', 'menit' => 45],
                ],
            ],
            // (i) Penerjemah
            'penerjemah' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::PENERJEMAH->value],
                'extra_names' => ['SIZIAH JUANDRI'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Penerjemah Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Menerjemahkan isi berita, artikel, dan konten publikasi website serta media sosial ke bahasa asing', 'menit' => 120],
                    ['nama_aktivitas' => 'Membantu menerjemahkan bahasa asing ketika wisatawan asing berkunjung atau pada kegiatan dinas', 'menit' => 150],
                    ['nama_aktivitas' => 'Berkomunikasi aktif dengan tim konten terkait permintaan dan penyesuaian istilah terjemahan', 'menit' => 45],
                    ['nama_aktivitas' => 'Memproses, menyunting ulang, dan mengirimkan hasil terjemahan ke pengelola website dan media sosial', 'menit' => 60],
                ],
            ],
            // (j) Administrasi
            'administrasi' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::ADMINISTRASI->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Administrasi Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Menyiapkan administrasi tim kreatif termasuk surat tugas ke lapangan untuk setiap kegiatan luar kantor', 'menit' => 60],
                    ['nama_aktivitas' => 'Mengirimkan link usulan konten ke bidang minimal 1 minggu sebelum jadwal tayang melalui WhatsApp Grup Dinas', 'menit' => 30],
                    ['nama_aktivitas' => 'Membantu mengomunikasikan proses produksi dan jadwal konten antar seksi/bidang', 'menit' => 60],
                    ['nama_aktivitas' => 'Menyusun dan mengirimkan PDF surat tugas, laporan mingguan (Senin), dan laporan bulanan (tanggal 2) tim kreatif', 'menit' => 120],
                ],
            ],
            // (k.1) Pengelola Geopark — Dokumentasi
            'pengelola_geopark_dokumentasi' => [
                'level' => 'staf',
                'roles' => [],
                'extra_names' => ['AFRINAL'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pengelola Geopark (Dokumentasi) Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Melakukan peliputan dokumentasi (foto dan video) kegiatan serta geosite untuk website dan media sosial Geopark', 'menit' => 240],
                    ['nama_aktivitas' => 'Mengumpulkan data lapangan dan menyusun narasi awal dokumentasi kawasan Geopark', 'menit' => 90],
                    ['nama_aktivitas' => 'Melakukan seleksi dan penyuntingan awal foto/video dokumentasi sesuai standar publikasi Geopark', 'menit' => 120],
                    ['nama_aktivitas' => 'Berkoordinasi teknis dengan Penanggung Jawab Kegiatan Bidang dan Koordinator Teknis terkait publikasi Geopark', 'menit' => 45],
                ],
            ],
            // (k.2) Pengelola Geopark — Penerjemah
            'pengelola_geopark_penerjemah' => [
                'level' => 'staf',
                'roles' => [],
                'extra_names' => ['MELGA SARI'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pengelola Geopark (Penerjemah) Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Menerjemahkan isi berita, profil geosite, dan konten publikasi untuk website dan media sosial Geopark', 'menit' => 120],
                    ['nama_aktivitas' => 'Membantu menerjemahkan bahasa asing ketika wisatawan atau peneliti asing berkunjung ke kawasan Geopark', 'menit' => 150],
                    ['nama_aktivitas' => 'Menyelaraskan dan mengirimkan hasil terjemahan materi Geopark kepada pengelola website/media sosial', 'menit' => 45],
                    ['nama_aktivitas' => 'Berkoordinasi teknis dengan Penanggung Jawab Kegiatan Bidang dan Koordinator Teknis terkait standar publikasi Geopark', 'menit' => 45],
                ],
            ],
            // (k.3) Pengelola Geopark — Pengelola Website & Media Sosial Geopark
            'pengelola_geopark_website_medsos' => [
                'level' => 'staf',
                'roles' => [],
                'extra_names' => ['SALSABILA MUKHSIS', 'RAHMAD'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pengelola Website dan Media Sosial Geopark Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengelola konten, menyusun jadwal tayang, dan mendesain publikasi khusus website dan media sosial Geopark', 'menit' => 150],
                    ['nama_aktivitas' => 'Menyusun takarir (caption) serta mengunggah materi berita/promosi pada website dan media sosial Geopark', 'menit' => 60],
                    ['nama_aktivitas' => 'Merespons komentar/pertanyaan pengunjung serta memantau interaksi akun media sosial Geopark', 'menit' => 45],
                    ['nama_aktivitas' => 'Berkoordinasi teknis dengan Penanggung Jawab Kegiatan Bidang dan Koordinator Teknis terkait standar publikasi Geopark', 'menit' => 45],
                ],
            ],
        ];

        $allTimKreatifUsers = User::query()
            ->whereNotNull('jabatan_tim_kreatif')
            ->get(['id', 'name', 'jabatan_id', 'bidang_id', 'jabatan_tim_kreatif']);

        $anggotaByRole = $allTimKreatifUsers->groupBy(fn (User $u): string => $u->jabatan_tim_kreatif?->value ?? '');
        $normalize = static fn (string $text): string => (string) preg_replace('/[^a-z]/', '', strtolower($text));

        $rencanaCount = 0;
        $aktivitasCount = 0;

        foreach ($data as $paket) {
            $rencanaAksi = RencanaAksi::updateOrCreate(
                [
                    'tahun' => $tahun,
                    'sasaran_sub_kegiatan' => 'Tugas Tambahan',
                    'level' => $paket['level'],
                    'rencana_aksi' => $paket['nama_rencana_aksi'],
                ],
                [
                    'sasaran_program' => $sasaranProgram,
                    'indikator' => 'Non_trajectory (kegiatan)',
                    'jenis' => 'non_trajectory',
                    'satuan' => 'kegiatan',
                ]
            );
            $rencanaCount++;

            $extraUsers = collect($paket['extra_names'] ?? [])
                ->map(function (string $nama) use ($allTimKreatifUsers, $normalize): ?User {
                    $needle = $normalize($nama);

                    return $allTimKreatifUsers->first(function (User $u) use ($needle, $normalize): bool {
                        $baseName = strstr($u->name, ',', true) ?: $u->name;

                        return $normalize($baseName) === $needle;
                    });
                })
                ->filter();

            $targetUsers = collect($paket['roles'])
                ->flatMap(fn (string $role) => $anggotaByRole->get($role, collect()))
                ->merge($extraUsers)
                ->unique('id');

            foreach ($targetUsers as $pegawai) {
                $keepNames = collect($paket['aktivitas'])->pluck('nama_aktivitas')->all();
                Aktivitas::query()
                    ->where('user_id', $pegawai->id)
                    ->where('rencana_aksi_id', $rencanaAksi->id)
                    ->whereNotIn('nama_aktivitas', $keepNames)
                    ->delete();

                foreach ($paket['aktivitas'] as $aktivitas) {
                    Aktivitas::updateOrCreate(
                        [
                            'user_id' => $pegawai->id,
                            'nama_aktivitas' => $aktivitas['nama_aktivitas'],
                        ],
                        [
                            'jabatan_id' => $pegawai->jabatan_id,
                            'bidang_id' => $pegawai->bidang_id,
                            'rencana_aksi_id' => $rencanaAksi->id,
                            'menit' => $aktivitas['menit'],
                            'status' => 'disetujui',
                        ]
                    );
                    $aktivitasCount++;
                }
            }
        }

        $this->command?->info("TugasTambahanTimKreatifSeeder selesai: {$rencanaCount} rencana aksi Tugas Tambahan & {$aktivitasCount} aktivitas anggota Tim Kreatif.");
    }
}
