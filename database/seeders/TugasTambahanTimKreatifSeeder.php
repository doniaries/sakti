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
                    ['nama_aktivitas' => 'Memberikan arahan kebijakan, visi, dan strategi pelaksanaan publikasi dan promosi dinas', 'menit' => 120],
                    ['nama_aktivitas' => 'Menetapkan arah komunikasi publik untuk membangun citra positif kelembagaan dinas', 'menit' => 120],
                    ['nama_aktivitas' => 'Melakukan evaluasi menyeluruh pelaksanaan publikasi dan promosi dinas', 'menit' => 120],
                    ['nama_aktivitas' => 'Turun langsung ke lokasi kegiatan untuk peninjauan dan supervisi publikasi', 'menit' => 240],
                ],
            ],
            // (b) Pengarah — Sekretaris
            'pengarah' => [
                'level' => 'kabid',
                'roles' => [JabatanTimKreatif::PENGARAH->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pengarah Tim Kreatif Publikasi dan Promosi Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengoordinasikan dan mengharmonisasikan pelaksanaan tugas publikasi lintas bidang', 'menit' => 120],
                    ['nama_aktivitas' => 'Memfasilitasi dukungan administrasi dan anggaran tim kreatif', 'menit' => 120],
                    ['nama_aktivitas' => 'Menerima dan memeriksa laporan berkala dari Penanggung Jawab Kegiatan Bidang', 'menit' => 60],
                    ['nama_aktivitas' => 'Turun langsung ke lokasi kegiatan untuk peninjauan dan supervisi publikasi', 'menit' => 240],
                    ['nama_aktivitas' => 'Melaksanakan instruksi teknis lain dari pimpinan terkait dokumentasi, publikasi, dan promosi', 'menit' => 120],
                ],
            ],
            // (c) Penanggung Jawab Kegiatan Bidang — Kepala Bidang
            'penanggung_jawab_bidang' => [
                'level' => 'kabid',
                'roles' => [JabatanTimKreatif::PENANGGUNG_JAWAB_BIDANG->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Penanggung Jawab Kegiatan Bidang Tim Kreatif Publikasi dan Promosi Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Menyusun dan merumuskan rencana kegiatan promosi/publikasi sesuai program kerja bidang', 'menit' => 120],
                    ['nama_aktivitas' => 'Memberikan instruksi dan arahan teknis kegiatan kepada Koordinator Teknis sebagai dasar pelaksanaan peliputan', 'menit' => 60],
                    ['nama_aktivitas' => 'Memastikan dan memantau kegiatan bidang telah terdokumentasi dan terpublikasi secara optimal melalui media resmi dinas', 'menit' => 60],
                    ['nama_aktivitas' => 'Menerima dan mengevaluasi laporan hasil publikasi yang disampaikan Koordinator Teknis', 'menit' => 60],
                    ['nama_aktivitas' => 'Turun langsung ke lokasi kegiatan untuk peninjauan, supervisi, dan penyelesaian kendala teknis publikasi bidang', 'menit' => 240],
                    ['nama_aktivitas' => 'Melaksanakan instruksi teknis lain dari pimpinan terkait dokumentasi, publikasi, dan promosi', 'menit' => 120],
                ],
            ],
            // (d) Koordinator Kegiatan — Jabatan Fungsional (Koordinator Teknis)
            'koordinator_kegiatan' => [
                'level' => 'jf',
                'roles' => [JabatanTimKreatif::KOORDINATOR_TIM_KREATIF->value, JabatanTimKreatif::KOORDINATOR_TEKNIS->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Koordinator Kegiatan (Koordinator Teknis) Tim Kreatif Publikasi dan Promosi Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Menjabarkan arahan Penanggung Jawab Kegiatan Bidang menjadi rencana kerja teknis publikasi dan dokumentasi', 'menit' => 120],
                    ['nama_aktivitas' => 'Menetapkan penugasan, membagi jadwal peliputan, dan memberikan target penyelesaian kerja kepada Koordinator Operasional', 'menit' => 60],
                    ['nama_aktivitas' => 'Melakukan pemeriksaan dan pengendalian mutu (quality control) tahap akhir hasil foto, video, desain grafis, naskah, dan konten sebelum dipublikasikan', 'menit' => 60],
                    ['nama_aktivitas' => 'Memberikan persetujuan tayang konten yang telah lolos pengendalian mutu', 'menit' => 30],
                    ['nama_aktivitas' => 'Menyelesaikan kendala operasional lintas unit di lapangan', 'menit' => 180],
                    ['nama_aktivitas' => 'Menyampaikan laporan pelaksanaan kegiatan kepada Penanggung Jawab Kegiatan Bidang', 'menit' => 120],
                    ['nama_aktivitas' => 'Melaksanakan instruksi teknis lain dari pimpinan terkait dokumentasi, publikasi, dan promosi', 'menit' => 120],
                ],
            ],
            // (e) Koordinator Website Dinas dan Website Wisata
            'koordinator_website' => [
                'level' => 'jf',
                'roles' => [JabatanTimKreatif::KOORDINATOR_WEBSITE->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Koordinator Website Dinas dan Website Wisata Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengelola dan bertanggung jawab atas Website Dinas dan Website Wisata', 'menit' => 120],
                    ['nama_aktivitas' => 'Menyusun jadwal peliputan, membagi tugas kerja harian, dan mengarahkan anggota tim website agar tidak terjadi tumpang tindih pekerjaan', 'menit' => 60],
                    ['nama_aktivitas' => 'Menghimpun, menyortir, dan melakukan pemeriksaan mutu awal bahan dokumentasi/naskah dari anggota tim', 'menit' => 60],
                    ['nama_aktivitas' => 'Menyerahkan hasil pekerjaan unit yang telah diverifikasi kepada Koordinator Teknis untuk proses persetujuan tayang', 'menit' => 30],
                    ['nama_aktivitas' => 'Melaksanakan instruksi teknis lain dari pimpinan terkait dokumentasi, publikasi, dan promosi', 'menit' => 120],
                ],
            ],
            // (f) Pelaksana Website Dinas dan Website Wisata
            'pelaksana_website' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::PELAKSANA_WEBSITE->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pelaksana Website Dinas dan Website Wisata Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengelola konten Website Dinas maupun Website Wisata secara harian', 'menit' => 120],
                    ['nama_aktivitas' => 'Mengumpulkan data bidang untuk bahan publikasi website', 'menit' => 120],
                    ['nama_aktivitas' => 'Menyusun naskah berita kegiatan untuk website', 'menit' => 120],
                    ['nama_aktivitas' => 'Menginput materi publikasi kegiatan ke dalam website', 'menit' => 60],
                    ['nama_aktivitas' => 'Melaksanakan instruksi teknis lain dari pimpinan terkait dokumentasi, publikasi, dan promosi', 'menit' => 120],
                ],
            ],
            // (g.1) Koordinator Dokumentasi Kegiatan
            'koordinator_dokumentasi' => [
                'level' => 'jf',
                'roles' => [JabatanTimKreatif::KOORDINATOR_DOKUMENTASI->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Koordinator Dokumentasi Kegiatan Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengoordinasikan dan bertanggung jawab atas pelaksanaan dokumentasi lapangan (foto dan video) seluruh kegiatan dinas', 'menit' => 300],
                    ['nama_aktivitas' => 'Mengatur jadwal penugasan peliputan tim di lapangan', 'menit' => 60],
                    ['nama_aktivitas' => 'Melakukan pemeriksaan awal hasil dokumentasi sebelum diserahkan kepada Koordinator Kegiatan', 'menit' => 60],
                    ['nama_aktivitas' => 'Melaksanakan instruksi teknis lain dari pimpinan terkait dokumentasi, publikasi, dan promosi', 'menit' => 120],
                ],
            ],
            // (g.2) Koordinator Media Sosial dan Desain Grafis
            'koordinator_medsos_desain' => [
                'level' => 'jf',
                'roles' => [JabatanTimKreatif::KOORDINATOR_MEDIA_SOSIAL->value, JabatanTimKreatif::KOORDINATOR_DESAIN_GRAFIS->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Koordinator Media Sosial dan Desain Grafis Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengelola standar visual akun media sosial dan standar desain promosi dinas', 'menit' => 120],
                    ['nama_aktivitas' => 'Melakukan pemeriksaan mutu awal karya desain tim sebelum diserahkan ke tahap publikasi', 'menit' => 45],
                    ['nama_aktivitas' => 'Mengarahkan pengelola media sosial dan desainer grafis dalam produksi konten', 'menit' => 60],
                    ['nama_aktivitas' => 'Melaksanakan instruksi teknis lain dari pimpinan terkait dokumentasi, publikasi, dan promosi', 'menit' => 120],
                ],
            ],
            // (h.1) Pengelola Media Sosial
            'pengelola_media_sosial' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::PENGELOLA_MEDIA_SOSIAL->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pengelola Media Sosial Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Merencanakan jadwal tayang (posting) dan mengelola akun media sosial resmi dinas dan pariwisata (Instagram, Facebook, TikTok, dan lainnya)', 'menit' => 60],
                    ['nama_aktivitas' => 'Menyusun narasi atau takarir (caption) publikasi media sosial', 'menit' => 60],
                    ['nama_aktivitas' => 'Mengunggah konten yang telah disetujui ke media sosial dinas', 'menit' => 30],
                    ['nama_aktivitas' => 'Merespons pertanyaan/komentar dan menjalin interaksi aktif dengan pengikut media sosial', 'menit' => 60],
                    ['nama_aktivitas' => 'Memantau analitik kinerja akun media sosial', 'menit' => 45],
                    ['nama_aktivitas' => 'Melaksanakan instruksi teknis lain dari pimpinan terkait dokumentasi, publikasi, dan promosi', 'menit' => 120],
                ],
            ],
            // (h.2) Desain Grafis
            'desain_grafis' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::DESAIN_GRAFIS->value],
                'extra_names' => ['AGUSMEDI ARIF', 'ROSY WITRIN', 'FITRI AMELIA'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Desainer Grafis Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Merancang dan memproduksi materi visual promosi (infografis, poster, banner, spanduk, dan desain feed media sosial)', 'menit' => 180],
                    ['nama_aktivitas' => 'Melakukan pengolahan warna (retouching), penyesuaian tata letak (layout), dan penyuntingan foto agar menarik', 'menit' => 120],
                    ['nama_aktivitas' => 'Menyesuaikan karya desain dengan identitas visual dan standar desain publikasi dinas', 'menit' => 60],
                    ['nama_aktivitas' => 'Melaksanakan instruksi teknis lain dari pimpinan terkait dokumentasi, publikasi, dan promosi', 'menit' => 120],
                ],
            ],
            // (h.3a) Dokumentasi — Fotografer
            'fotografer' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::DOKUMENTASI->value],
                'extra_names' => ['JENI ZULMARLY KAMIL', 'ANDRA PEBTALIYUS', 'NAZHIRUL RIFQI ALNEV', 'PRADIUTAMA JULIYANSA', 'FARHANUL HUDA'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Fotografer Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Turun langsung ke lokasi kegiatan untuk dokumentasi kegiatan (mengambil gambar atau video)', 'menit' => 300],
                    ['nama_aktivitas' => 'Mengumpulkan data awal untuk bahan narasi/wawancara dan menyerahkan bahan mentah (raw data) kepada Koordinator Kegiatan', 'menit' => 60],
                    ['nama_aktivitas' => 'Melakukan desain pengolahan foto dan penyuntingan (editing) video sesuai standar publikasi', 'menit' => 180],
                    ['nama_aktivitas' => 'Membuat narasi terkait kegiatan yang didokumentasikan', 'menit' => 60],
                    ['nama_aktivitas' => 'Melaksanakan instruksi teknis lain dari pimpinan terkait dokumentasi, publikasi, dan promosi', 'menit' => 120],
                ],
            ],
            // (h.3b) Dokumentasi — Videografer
            'videografer' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::DOKUMENTASI->value],
                'extra_names' => ['JENI ZULMARLY KAMIL', 'ANDRA PEBTALIYUS', 'NAZHIRUL RIFQI ALNEV', 'PRADIUTAMA JULIYANSA', 'FARHANUL HUDA'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Videografer Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Turun langsung ke lokasi kegiatan untuk merekam gambar bergerak (video) dan audio dari seluruh rangkaian acara', 'menit' => 300],
                    ['nama_aktivitas' => 'Mengumpulkan bahan tayangan pendukung, seperti wawancara singkat (doorstop/testimoni) di lapangan', 'menit' => 120],
                    ['nama_aktivitas' => 'Melakukan penyuntingan (editing) video, merangkai transisi, dan menambahkan elemen audio/teks menjadi luaran konten promosi, reels, atau dokumenter singkat', 'menit' => 240],
                    ['nama_aktivitas' => 'Membuat narasi terkait kegiatan yang didokumentasikan', 'menit' => 60],
                    ['nama_aktivitas' => 'Melaksanakan instruksi teknis lain dari pimpinan terkait dokumentasi, publikasi, dan promosi', 'menit' => 120],
                ],
            ],
            // (i) Penerjemah
            'penerjemah' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::PENERJEMAH->value],
                'extra_names' => ['SIZIAH JUANDRI'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Penerjemah Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Menerjemahkan isi berita dan konten lainnya di website dan media sosial', 'menit' => 120],
                    ['nama_aktivitas' => 'Membantu menerjemahkan bahasa asing ketika wisatawan asing berkunjung', 'menit' => 120],
                    ['nama_aktivitas' => 'Berkomunikasi aktif ketika ada permintaan terjemahan konten untuk website dan media sosial', 'menit' => 60],
                    ['nama_aktivitas' => 'Memproses dan mengirimkan hasil terjemahan kepada pengelola website dan media sosial', 'menit' => 60],
                    ['nama_aktivitas' => 'Melaksanakan instruksi teknis lain dari pimpinan terkait dokumentasi, publikasi, dan promosi', 'menit' => 120],
                ],
            ],
            // (j) Administrasi
            'administrasi' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::ADMINISTRASI->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Administrasi Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Menyiapkan administrasi tim kreatif, seperti surat tugas ke lapangan setiap ada kegiatan di luar kantor', 'menit' => 60],
                    ['nama_aktivitas' => 'Mengirimkan link usulan konten ke bidang minimal 1 minggu sebelum jadwal tayang melalui WhatsApp Grup Dinas', 'menit' => 30],
                    ['nama_aktivitas' => 'Membantu mengomunikasikan proses konten antar seksi/bidang', 'menit' => 60],
                    ['nama_aktivitas' => 'Mengirimkan PDF surat tugas, laporan mingguan setiap hari Senin, dan laporan bulanan setiap tanggal 2 melalui WhatsApp Grup Tim Kreatif', 'menit' => 60],
                    ['nama_aktivitas' => 'Membuat laporan mingguan dan bulanan tim kreatif dan mengirimkannya melalui WhatsApp Grup Dinas', 'menit' => 120],
                    ['nama_aktivitas' => 'Melaksanakan instruksi teknis lain dari pimpinan terkait dokumentasi, publikasi, dan promosi', 'menit' => 120],
                ],
            ],
            // (k.1) Pengelola Geopark — Dokumentasi
            'pengelola_geopark_dokumentasi' => [
                'level' => 'staf',
                'roles' => [],
                'extra_names' => ['AFRINAL'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pengelola Geopark (Dokumentasi) Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Melakukan peliputan dokumentasi (foto dan video) kegiatan serta objek wisata untuk kebutuhan website dan media sosial Geopark', 'menit' => 300],
                    ['nama_aktivitas' => 'Mengumpulkan bahan dokumentasi dan narasi awal kegiatan Geopark untuk publikasi', 'menit' => 120],
                    ['nama_aktivitas' => 'Berkoordinasi secara teknis dengan Penanggung Jawab Kegiatan Bidang dan Koordinator Teknis terkait standar publikasi Geopark', 'menit' => 60],
                    ['nama_aktivitas' => 'Melaksanakan tugas teknis lain yang diberikan pimpinan terkait publikasi Geopark', 'menit' => 120],
                ],
            ],
            // (k.2) Pengelola Geopark — Penerjemah
            'pengelola_geopark_penerjemah' => [
                'level' => 'staf',
                'roles' => [],
                'extra_names' => ['MELGA SARI'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pengelola Geopark (Penerjemah) Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Menerjemahkan isi berita, narasi, dan konten publikasi untuk website dan media sosial Geopark', 'menit' => 120],
                    ['nama_aktivitas' => 'Membantu menerjemahkan bahasa asing ketika wisatawan asing berkunjung ke kawasan Geopark', 'menit' => 120],
                    ['nama_aktivitas' => 'Berkoordinasi secara teknis dengan Penanggung Jawab Kegiatan Bidang dan Koordinator Teknis terkait standar publikasi Geopark', 'menit' => 60],
                    ['nama_aktivitas' => 'Melaksanakan tugas teknis lain yang diberikan pimpinan terkait publikasi Geopark', 'menit' => 120],
                ],
            ],
            // (k.3) Pengelola Geopark — Pengelola Website & Media Sosial Geopark
            'pengelola_geopark_website_medsos' => [
                'level' => 'staf',
                'roles' => [],
                'extra_names' => ['SALSABILA MUKHSIS', 'RAHMAD'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pengelola Website dan Media Sosial Geopark Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengelola konten, menyusun jadwal tayang, dan mendesain publikasi khusus untuk website dan media sosial Geopark', 'menit' => 180],
                    ['nama_aktivitas' => 'Mengunggah materi publikasi dan merespons interaksi pada website serta media sosial Geopark', 'menit' => 60],
                    ['nama_aktivitas' => 'Berkoordinasi secara teknis dengan Penanggung Jawab Kegiatan Bidang dan Koordinator Teknis terkait standar publikasi Geopark', 'menit' => 60],
                    ['nama_aktivitas' => 'Melaksanakan tugas teknis lain yang diberikan pimpinan terkait publikasi Geopark', 'menit' => 120],
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
