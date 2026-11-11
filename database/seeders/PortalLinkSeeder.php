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
                'judul' => 'Website Resmi Disparpora',
                'deskripsi' => 'Portal publikasi, profil kedinasan, berita pariwisata, kepemudaan dan olahraga Kabupaten Sijunjung',
                'url' => 'https://disparpora.sijunjung.go.id/',
                'target' => '_blank',
                'warna' => 'emerald',
                'bentuk' => 'squircle',
                'kategori' => 'eksternal',
                'badge' => 'Website Resmi',
                'urutan' => 1,
                'is_aktif' => true,
                'akses_role' => 'semua',
            ],
            [
                'judul' => 'Geopark Silokek Ranah Minang',
                'deskripsi' => 'Situs resmi Geopark Nasional Ranah Minang Silokek: geowisata, budaya nagari, keanekaragaman hayati, dan informasi destinasi',
                'url' => 'https://ranahminangsilokekgeopark.id/',
                'target' => '_blank',
                'warna' => 'teal',
                'bentuk' => 'squircle',
                'kategori' => 'eksternal',
                'badge' => 'Geopark Silokek',
                'urutan' => 2,
                'is_aktif' => true,
                'akses_role' => 'semua',
            ],
            [
                'judul' => 'Presensi Mobile Pegawai',
                'deskripsi' => 'Aplikasi presensi mandiri ASN & Non-ASN: Tap masuk & pulang GPS geofencing, izin dinas luar, dan rekap mandiri',
                'url' => '/mobile',
                'target' => '_self',
                'warna' => 'sky',
                'bentuk' => 'squircle',
                'kategori' => 'kepegawaian',
                'badge' => 'PWA Mobile',
                'urutan' => 3,
                'is_aktif' => true,
                'akses_role' => 'semua',
            ],
            [
                'judul' => 'Laporan Kinerja Harian (LHK)',
                'deskripsi' => 'Pencatatan real-time aktivitas pekerjaan harian, durasi menit, bukti foto kegiatan, dan verifikasi atasan',
                'url' => '/mobile/lhk',
                'target' => '_self',
                'warna' => 'indigo',
                'bentuk' => 'squircle',
                'kategori' => 'kinerja',
                'badge' => 'E-Kinerja',
                'urutan' => 4,
                'is_aktif' => true,
                'akses_role' => 'semua',
            ],
            [
                'judul' => 'Keuangan & Pengajuan SPJ',
                'deskripsi' => 'Pengadaan barang/jasa, pengajuan Surat Pesanan, verifikasi berjenjang PPTK, PPK, PA, hingga penerbitan NPD kas',
                'url' => '/admin/pengajuan-spjs',
                'target' => '_self',
                'warna' => 'amber',
                'bentuk' => 'squircle',
                'kategori' => 'keuangan',
                'badge' => 'Modul SPJ',
                'urutan' => 5,
                'is_aktif' => true,
                'akses_role' => 'semua',
            ],
            [
                'judul' => 'Manajemen ASN & Kepegawaian',
                'deskripsi' => 'Master data pegawai ASN, struktur bidang dinas, shift jam kerja, riwayat log absensi, dan integrasi mesin ZKTeco',
                'url' => '/admin/users',
                'target' => '_self',
                'warna' => 'purple',
                'bentuk' => 'squircle',
                'kategori' => 'kepegawaian',
                'badge' => 'Master ASN',
                'urutan' => 6,
                'is_aktif' => true,
                'akses_role' => 'semua',
            ],
            [
                'judul' => 'Lacak Dokumen SPJ (QR Code)',
                'deskripsi' => 'Layanan publik pelacakan keaslian lembar transaksi resmi, kwitansi, nota pesanan, dan verifikasi barcode SPJ',
                'url' => '/lacak-dokumen',
                'target' => '_self',
                'warna' => 'slate',
                'bentuk' => 'squircle',
                'kategori' => 'keuangan',
                'badge' => 'Verifikasi QR',
                'urutan' => 7,
                'is_aktif' => true,
                'akses_role' => 'semua',
            ],
            [
                'judul' => 'Panel Admin & Konfigurasi',
                'deskripsi' => 'Pusat administrasi sistem SAKTI, konfigurasi titik GPS kantor, profil dinas, serta manajemen hak akses pengguna',
                'url' => '/admin',
                'target' => '_self',
                'warna' => 'rose',
                'bentuk' => 'squircle',
                'kategori' => 'administrasi',
                'badge' => 'Panel Pejabat',
                'urutan' => 8,
                'is_aktif' => true,
                'akses_role' => 'admin',
            ],
        ];

        // Refresh portal links cleanly with new data
        PortalLink::truncate();

        foreach ($links as $link) {
            PortalLink::create($link);
        }
    }
}
