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
                    ['nama_aktivitas' => 'Melakukan evaluasi menyeluruh pelaksanaan publikasi dan promosi dinas', 'menit' => 120],
                ],
            ],
            // (b) Pengarah — Sekretaris
            'pengarah' => [
                'level' => 'kabid',
                'roles' => [JabatanTimKreatif::PENGARAH->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pengarah Tim Kreatif Publikasi dan Promosi Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengoordinasikan dan mengharmonisasikan pelaksanaan tugas publikasi lintas bidang', 'menit' => 120],
                    ['nama_aktivitas' => 'Memfasilitasi dukungan administrasi, anggaran, dan evaluasi laporan berkala tim kreatif', 'menit' => 120],
                ],
            ],
            // (c) Penanggung Jawab Kegiatan Bidang — Kepala Bidang
            'penanggung_jawab_bidang' => [
                'level' => 'kabid',
                'roles' => [JabatanTimKreatif::PENANGGUNG_JAWAB_BIDANG->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Penanggung Jawab Kegiatan Bidang Tim Kreatif Publikasi dan Promosi Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Menyusun rencana kegiatan promosi/publikasi serta memberi arahan teknis peliputan kepada Koordinator Teknis', 'menit' => 120],
                    ['nama_aktivitas' => 'Memastikan kegiatan bidang terdokumentasi/terpublikasi optimal dan mengevaluasi laporan hasil publikasi', 'menit' => 60],
                ],
            ],
            // (d) Koordinator Kegiatan — Jabatan Fungsional (Koordinator Teknis)
            'koordinator_kegiatan' => [
                'level' => 'jf',
                'roles' => [JabatanTimKreatif::KOORDINATOR_TIM_KREATIF->value, JabatanTimKreatif::KOORDINATOR_TEKNIS->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Koordinator Kegiatan (Koordinator Teknis) Tim Kreatif Publikasi dan Promosi Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Menjabarkan arahan pimpinan menjadi rencana kerja teknis, membagi jadwal peliputan, dan menyelesaikan kendala lapangan', 'menit' => 120],
                    ['nama_aktivitas' => 'Melakukan quality control tahap akhir konten sebelum tayang dan melaporkan pelaksanaan kegiatan', 'menit' => 60],
                ],
            ],
            // (e) Koordinator Website Dinas dan Website Wisata
            'koordinator_website' => [
                'level' => 'jf',
                'roles' => [JabatanTimKreatif::KOORDINATOR_WEBSITE->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Koordinator Website Dinas dan Website Wisata Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengelola Website Dinas dan Website Wisata serta menyusun jadwal peliputan dan pembagian tugas harian tim', 'menit' => 120],
                    ['nama_aktivitas' => 'Menghimpun, menyortir, dan memverifikasi bahan dokumentasi/naskah untuk persetujuan tayang Koordinator Teknis', 'menit' => 60],
                ],
            ],
            // (f) Pelaksana Website Dinas dan Website Wisata
            'pelaksana_website' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::PELAKSANA_WEBSITE->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pelaksana Website Dinas dan Website Wisata Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengelola konten Website Dinas maupun Website Wisata secara harian', 'menit' => 120],
                    ['nama_aktivitas' => 'Mengumpulkan data bidang, menyusun naskah berita, dan menginput materi publikasi ke website', 'menit' => 120],
                ],
            ],
            // (g.1) Koordinator Dokumentasi Kegiatan
            'koordinator_dokumentasi' => [
                'level' => 'jf',
                'roles' => [JabatanTimKreatif::KOORDINATOR_DOKUMENTASI->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Koordinator Dokumentasi Kegiatan Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengoordinasikan pelaksanaan dokumentasi lapangan (foto/video) seluruh kegiatan dinas dan jadwal penugasan tim', 'menit' => 240],
                    ['nama_aktivitas' => 'Melakukan pemeriksaan awal hasil dokumentasi sebelum diserahkan kepada Koordinator Kegiatan', 'menit' => 60],
                ],
            ],
            // (g.2) Koordinator Media Sosial dan Desain Grafis
            'koordinator_medsos_desain' => [
                'level' => 'jf',
                'roles' => [JabatanTimKreatif::KOORDINATOR_MEDIA_SOSIAL->value, JabatanTimKreatif::KOORDINATOR_DESAIN_GRAFIS->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Koordinator Media Sosial dan Desain Grafis Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Mengelola standar visual akun media sosial dan desain promosi serta mengarahkan produksi konten tim', 'menit' => 120],
                    ['nama_aktivitas' => 'Melakukan pemeriksaan mutu awal karya desain tim sebelum diserahkan ke tahap publikasi', 'menit' => 60],
                ],
            ],
            // (h.1) Pengelola Media Sosial
            'pengelola_media_sosial' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::PENGELOLA_MEDIA_SOSIAL->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pengelola Media Sosial Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Merencanakan jadwal tayang (posting), menyusun takarir (caption), dan mengelola akun media sosial resmi dinas', 'menit' => 60],
                    ['nama_aktivitas' => 'Merespons komentar/interaksi pengikut serta memantau analitik kinerja akun media sosial', 'menit' => 60],
                ],
            ],
            // (h.2) Desain Grafis
            'desain_grafis' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::DESAIN_GRAFIS->value],
                'extra_names' => ['AGUSMEDI ARIF', 'ROSY WITRIN', 'FITRI AMELIA'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Desainer Grafis Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Merancang dan memproduksi materi visual promosi (infografis, poster, banner, spanduk, dan feed media sosial)', 'menit' => 180],
                    ['nama_aktivitas' => 'Melakukan pengolahan warna (retouching), tata letak (layout), dan penyuntingan foto sesuai standar visual dinas', 'menit' => 120],
                ],
            ],
            // (h.3a) Dokumentasi — Fotografer
            'fotografer' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::DOKUMENTASI->value],
                'extra_names' => ['JENI ZULMARLY KAMIL', 'ANDRA PEBTALIYUS', 'NAZHIRUL RIFQI ALNEV', 'PRADIUTAMA JULIYANSA', 'FARHANUL HUDA'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Fotografer Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Turun langsung ke lokasi kegiatan untuk dokumentasi foto dan pengumpulan bahan narasi/raw data', 'menit' => 300],
                    ['nama_aktivitas' => 'Melakukan pengolahan foto dan penyusunan narasi terkait kegiatan sesuai standar publikasi', 'menit' => 120],
                ],
            ],
            // (h.3b) Dokumentasi — Videografer
            'videografer' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::DOKUMENTASI->value],
                'extra_names' => ['JENI ZULMARLY KAMIL', 'ANDRA PEBTALIYUS', 'NAZHIRUL RIFQI ALNEV', 'PRADIUTAMA JULIYANSA', 'FARHANUL HUDA'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Videografer Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Turun langsung ke lokasi kegiatan untuk merekam video, audio, dan wawancara singkat (doorstop/testimoni)', 'menit' => 300],
                    ['nama_aktivitas' => 'Melakukan penyuntingan (editing) video, transisi, audio/teks, dan narasi menjadi konten promosi/reels', 'menit' => 240],
                ],
            ],
            // (i) Penerjemah
            'penerjemah' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::PENERJEMAH->value],
                'extra_names' => ['SIZIAH JUANDRI'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Penerjemah Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Menerjemahkan isi berita dan konten website/media sosial serta mengirimkannya ke pengelola publikasi', 'menit' => 120],
                    ['nama_aktivitas' => 'Membantu menerjemahkan bahasa asing ketika wisatawan asing berkunjung', 'menit' => 120],
                ],
            ],
            // (j) Administrasi
            'administrasi' => [
                'level' => 'staf',
                'roles' => [JabatanTimKreatif::ADMINISTRASI->value],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Administrasi Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Menyiapkan administrasi surat tugas lapangan, link usulan konten, dan komunikasi proses konten antar seksi', 'menit' => 60],
                    ['nama_aktivitas' => 'Membuat dan mengirimkan PDF surat tugas, laporan mingguan (Senin), dan laporan bulanan (tanggal 2) tim kreatif', 'menit' => 120],
                ],
            ],
            // (k.1) Pengelola Geopark — Dokumentasi
            'pengelola_geopark_dokumentasi' => [
                'level' => 'staf',
                'roles' => [],
                'extra_names' => ['AFRINAL'],
                'nama_rencana_aksi' => 'Terlaksananya tugas tambahan sebagai Pengelola Geopark (Dokumentasi) Tim Kreatif Disparpora',
                'aktivitas' => [
                    ['nama_aktivitas' => 'Melakukan peliputan dokumentasi (foto dan video) kegiatan serta objek wisata untuk website dan media sosial Geopark', 'menit' => 300],
                    ['nama_aktivitas' => 'Mengumpulkan bahan dokumentasi dan narasi awal kegiatan Geopark serta berkoordinasi standar publikasi', 'menit' => 120],
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
