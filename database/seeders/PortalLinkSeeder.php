<?php

namespace Database\Seeders;

use App\Models\PortalLink;
use Illuminate\Database\Seeder;

class PortalLinkSeeder extends Seeder
{
    public function run(): void
    {
        $links = [
            [
                'judul' => 'Keuangan & SPJ',
                'deskripsi' => 'Pengajuan Surat Pesanan, verifikasi berjenjang SPJ, & pencairan anggaran',
                'icon' => 'fa-solid fa-file-invoice-dollar',
                'url' => '/admin/pengajuan-spjs',
                'target' => '_self',
                'warna' => 'amber',
                'bentuk' => 'squircle',
                'kategori' => 'keuangan',
                'badge' => 'Modul SPJ',
                'urutan' => 1,
                'is_aktif' => true,
                'akses_role' => 'semua',
            ],
            [
                'judul' => 'Kepegawaian & Presensi',
                'deskripsi' => 'Rekap presensi harian, master shift kerja, biodata pegawai, & mesin absensi',
                'icon' => 'fa-solid fa-users-gear',
                'url' => '/admin/users',
                'target' => '_self',
                'warna' => 'emerald',
                'bentuk' => 'squircle',
                'kategori' => 'kepegawaian',
                'badge' => 'Master ASN',
                'urutan' => 2,
                'is_aktif' => true,
                'akses_role' => 'semua',
            ],
            [
                'judul' => 'Presensi Mobile Pegawai',
                'deskripsi' => 'PWA Pegawai untuk tap absensi mandiri GPS, rekap riwayat, & profil MyASN',
                'icon' => 'fa-solid fa-mobile-screen-button',
                'url' => '/mobile',
                'target' => '_self',
                'warna' => 'sky',
                'bentuk' => 'squircle',
                'kategori' => 'layanan_utama',
                'badge' => 'Aplikasi PWA',
                'urutan' => 3,
                'is_aktif' => true,
                'akses_role' => 'semua',
            ],
            [
                'judul' => 'Laporan Kinerja LHK',
                'deskripsi' => 'Pengisian dan verifikasi laporan aktivitas kerja harian pegawai ASN terpadu',
                'icon' => 'fa-solid fa-clipboard-check',
                'url' => '/mobile/lhk',
                'target' => '_self',
                'warna' => 'indigo',
                'bentuk' => 'squircle',
                'kategori' => 'layanan_utama',
                'badge' => 'E-Kinerja',
                'urutan' => 4,
                'is_aktif' => true,
                'akses_role' => 'semua',
            ],
            [
                'judul' => 'Panel Administrator',
                'deskripsi' => 'Gerbang login utama pejabat dinas, bendahara, verifikator, & super admin',
                'icon' => 'fa-solid fa-shield-halved',
                'url' => '/admin',
                'target' => '_self',
                'warna' => 'rose',
                'bentuk' => 'squircle',
                'kategori' => 'administrasi',
                'badge' => 'Portal Login',
                'urutan' => 5,
                'is_aktif' => true,
                'akses_role' => 'admin',
            ],
            [
                'judul' => 'Dokumen & Regulasi',
                'deskripsi' => 'Kumpulan panduan teknis, SK penetapan, Perbup, dan SOP operasional kedinasan',
                'icon' => 'fa-solid fa-book-bookmark',
                'url' => '#',
                'target' => '_blank',
                'warna' => 'teal',
                'bentuk' => 'pill',
                'kategori' => 'dokumen_lampiran',
                'badge' => 'Publikasi',
                'urutan' => 6,
                'is_aktif' => true,
                'akses_role' => 'semua',
            ],
        ];

        foreach ($links as $link) {
            PortalLink::updateOrCreate(
                ['judul' => $link['judul']],
                $link
            );
        }
    }
}
