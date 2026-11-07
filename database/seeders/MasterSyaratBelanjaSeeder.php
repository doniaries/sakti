<?php

namespace Database\Seeders;

use App\Models\MasterSyaratBelanja;
use Illuminate\Database\Seeder;

class MasterSyaratBelanjaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // E-Katalog (E-Purchasing LKPP)
            [
                'jenis_belanja' => 'semua',
                'metode_pengadaan' => 'e_katalog',
                'nama_syarat' => 'Bundel Dokumen E-Purchasing LKPP (Surat Pesanan, BAST & Invoice E-Proc)',
                'keterangan' => '[E-Katalog] Unduh file bundel resmi PDF lengkap dari portal E-Katalog LKPP (Surat Pesanan, BAST, dan Faktur E-Proc).',
                'is_required' => true,
                'urutan' => 1,
                'aktif' => true,
            ],
            [
                'jenis_belanja' => 'semua',
                'metode_pengadaan' => 'e_katalog',
                'nama_syarat' => 'Foto / Bukti Penerimaan Barang E-Katalog',
                'keterangan' => '[E-Katalog] Foto fisik barang saat tiba dan diperiksa oleh tim penerima di kantor dinas.',
                'is_required' => true,
                'urutan' => 2,
                'aktif' => true,
            ],
            [
                'jenis_belanja' => 'semua',
                'metode_pengadaan' => 'e_katalog',
                'nama_syarat' => 'Surat Pernyataan Tanggung Jawab Mutlak (SPTJM) E-Purchasing',
                'keterangan' => '[E-Katalog] SPTJM kebenaran pengadaan e-purchasing dan penerimaan barang bermaterai.',
                'is_required' => false,
                'urutan' => 3,
                'aktif' => true,
            ],

            // Non-Katalog: Belanja Barang & Jasa
            [
                'jenis_belanja' => 'barang_jasa',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Scan Nota Pesanan Bertandatangan Basah (PPTK & Toko)',
                'keterangan' => '[Non-Katalog] Scan lembar fisik Nota Pesanan resmi yang telah ditandatangani basah oleh PPTK (Kabid) dan dicap/ditandatangani Rekanan Toko.',
                'is_required' => true,
                'urutan' => 1,
                'aktif' => true,
            ],
            [
                'jenis_belanja' => 'barang_jasa',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Scan Kwitansi Bertandatangan Basah (Rekanan & PPTK)',
                'keterangan' => '[Non-Katalog] Scan lembar fisik Kwitansi Pembayaran yang telah ditandatangani basah oleh Rekanan Toko (bermaterai) dan PPTK (Kabid).',
                'is_required' => true,
                'urutan' => 2,
                'aktif' => true,
            ],
            [
                'jenis_belanja' => 'barang_jasa',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Faktur / Nota Asli Penjual',
                'keterangan' => 'Nota/faktur asli dari toko rekanan yang memuat rincian pembelian',
                'is_required' => true,
                'urutan' => 3,
                'aktif' => true,
            ],
            [
                'jenis_belanja' => 'barang_jasa',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Foto Fisik Barang / Pengadaan',
                'keterangan' => 'Foto dokumentasi barang yang telah dibeli',
                'is_required' => true,
                'urutan' => 4,
                'aktif' => true,
            ],
            [
                'jenis_belanja' => 'barang_jasa',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Berita Acara Serah Terima (BAST) Rekanan',
                'keterangan' => 'Jika ada BAST terpisah dari rekanan/toko',
                'is_required' => false,
                'urutan' => 5,
                'aktif' => true,
            ],

            // Makan & Minum Rapat
            [
                'jenis_belanja' => 'makan_minum',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Surat Undangan / Nota Dinas Rapat',
                'keterangan' => 'Undangan resmi pelaksanaan rapat kedinasan',
                'is_required' => true,
                'urutan' => 1,
                'aktif' => true,
            ],
            [
                'jenis_belanja' => 'makan_minum',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Daftar Hadir Peserta Rapat (Bertandatangan)',
                'keterangan' => 'Scan daftar hadir peserta rapat lengkap dengan tanda tangan',
                'is_required' => true,
                'urutan' => 2,
                'aktif' => true,
            ],
            [
                'jenis_belanja' => 'makan_minum',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Notulensi Hasil Rapat',
                'keterangan' => 'Risalah ringkas atau laporan hasil pembahasan rapat',
                'is_required' => true,
                'urutan' => 3,
                'aktif' => true,
            ],
            [
                'jenis_belanja' => 'makan_minum',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Foto Dokumentasi Pelaksanaan Rapat',
                'keterangan' => 'Foto suasana rapat dan konsumsi yang disajikan',
                'is_required' => true,
                'urutan' => 4,
                'aktif' => true,
            ],
            [
                'jenis_belanja' => 'makan_minum',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Nota / Kwitansi Rumah Makan / Katering',
                'keterangan' => 'Nota rincian jumlah snack box / nasi kotak dari penyedia',
                'is_required' => true,
                'urutan' => 5,
                'aktif' => true,
            ],

            // SPPD (Perjalanan Dinas)
            [
                'jenis_belanja' => 'sppd',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Surat Perintah Tugas (SPT)',
                'keterangan' => 'SPT resmi bertandatangan Kepala Dinas / Pimpinan',
                'is_required' => true,
                'urutan' => 1,
                'aktif' => true,
            ],
            [
                'jenis_belanja' => 'sppd',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Lembar SPPD Bertandatangan & Cap Tujuan',
                'keterangan' => 'Lembar SPPD halaman belakang yang dicap dan ditandatangani pejabat instansi tujuan',
                'is_required' => true,
                'urutan' => 2,
                'aktif' => true,
            ],
            [
                'jenis_belanja' => 'sppd',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Laporan Hasil Perjalanan Dinas',
                'keterangan' => 'Laporan pelaksanaan tugas kedinasan yang dibuat pelaksana tugas',
                'is_required' => true,
                'urutan' => 3,
                'aktif' => true,
            ],
            [
                'jenis_belanja' => 'sppd',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Foto Dokumentasi Kegiatan di Lokasi Tujuan',
                'keterangan' => 'Foto kegiatan di tempat tujuan perjalanan dinas',
                'is_required' => true,
                'urutan' => 4,
                'aktif' => true,
            ],
            [
                'jenis_belanja' => 'sppd',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Tiket Transportasi / Boarding Pass / Bukti Penginapan',
                'keterangan' => 'Kuitansi hotel, karcis travel, boarding pass pesawat (khusus luar daerah)',
                'is_required' => false,
                'urutan' => 5,
                'aktif' => true,
            ],

            // BBM (Bahan Bakar Minyak)
            [
                'jenis_belanja' => 'bbm',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Struk / Kupon Asli Pembelian BBM',
                'keterangan' => 'Struk SPBU / kupon BBM resmi sesuai kuota liter yang disetujui',
                'is_required' => true,
                'urutan' => 1,
                'aktif' => true,
            ],
            [
                'jenis_belanja' => 'bbm',
                'metode_pengadaan' => 'non_katalog',
                'nama_syarat' => 'Foto Odometer / Kendaraan Dinas',
                'keterangan' => 'Foto plat nomor dan odometer kendaraan sebelum dan sesudah tugas',
                'is_required' => false,
                'urutan' => 2,
                'aktif' => true,
            ],
        ];

        foreach ($data as $item) {
            MasterSyaratBelanja::updateOrCreate(
                [
                    'jenis_belanja' => $item['jenis_belanja'],
                    'metode_pengadaan' => $item['metode_pengadaan'],
                    'nama_syarat' => $item['nama_syarat'],
                ],
                $item
            );
        }
    }
}

