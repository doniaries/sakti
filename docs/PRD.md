# PRD — SAKTI (Sistem Administrasi Kinerja Terintegrasi)

> **Instansi**: Dinas Pariwisata, Pemuda dan Olahraga (Disparpora) Kabupaten Sijunjung  
> **Tujuan**: Menyatukan administrasi kepegawaian, presensi ASN & Non-ASN, Laporan Harian Kinerja (LHK), Tim Kreatif & Surat Tugas elektronik, serta pengelolaan anggaran/SPJ keuangan daerah dalam satu sistem terpadu.

---

## 1. Aktor & 8 Peran Sistem (RBAC)

| Role | Akses Panel | Tanggung Jawab Utama |
| :--- | :--- | :--- |
| `super_admin` | Admin + Mobile | Pengelolaan penuh sistem, RBAC Shield, Menu Builder, developer account switcher (mode server). |
| `admin` | Admin + Mobile | Pengelola administrasi kepegawaian, master data, dan operasional dinas. |
| `pa` (Kepala Dinas) | Admin + Mobile | Pimpinan tertinggi (`bidang_id = null`, `atasan_id = null`), persetujuan akhir SPJ, cuti, dan penandatangan Surat Tugas. |
| `ppk` | Admin + Mobile | Verifikasi kelengkapan & kepatuhan SPJ keuangan. |
| `pptk` (Kepala Bidang) | Admin + Mobile | Pengendali kegiatan bidang, verifikasi LHK/izin bawahan, & pengesahan Surat Pesanan/SPJ tingkat bidang. |
| `bendahara` | Admin + Mobile | Pencairan dana (NPD), pengelolaan pajak (PPN/PPh), LPJ Fungsional & LRFK. |
| `verifikator` | Admin + Mobile | Pemeriksa dokumen pendukung SPJ/keuangan. |
| `pegawai` | Dashboard Pegawai + Mobile PWA | Presensi masuk/pulang, pengajuan izin/cuti, pengisian LHK, profil mandiri, serta modul Tim Kreatif (bila terdaftar). |

---

## 2. Modul & Fitur Utama

### A. Master Kepegawaian & Profil Instansi
- **Profil Instansi (`Instansi`)**: Nama dinas, kode SKPD, alamat resmi, Kepala Dinas (`kepala_id`), dan logo instansi (disk `public`), tersinkron dua arah dengan `PengaturanAplikasi`.
- **Bidang (`Bidang`)**: Menyimpan `nama`, **`singkatan`** (diinput manual, misal `PP`, `Sekre`, `PAR`, `EKR`, `PO`), `slug`, dan `kepala_id`. Kolom `singkatan` menjadi acuan resmi penomoran SPJ dan dokumen dinas. Perubahan nama bidang otomatis diikuti seluruh pegawai karena relasi memakai `bidang_id`.
- **Pegawai (`User`)**: Biodata lengkap ala MyASN (NIP, golongan, jabatan, eselon, pendidikan, NPWP, Taspen, `status_kerja`: `wfo`/`wfh`, `atasan_id`, `jabatan_tim_kreatif`, kuota cuti).

### B. Tim Kreatif & Surat Tugas Elektronik (TTE SAKTI)
- **Tim Kreatif (`App\Filament\Pages\TimKreatif`)**:
  - Menampilkan daftar anggota sesuai SK Kepala Dinas Nomor `188.45/28/KPTS-DISPARPORA/2026`.
  - Urutan baris mengikuti hierarki SK (Pembina ➔ Pengarah ➔ PJ Bidang ➔ Koordinator Tim Kreatif ➔ Koordinator Teknis ➔ Website ➔ Dokumentasi/Desain/Medsos ➔ Administrasi ➔ Pengelola Geopark), **bukan** abjad nama.
  - Dilengkapi tab filter kelompok jabatan SK.
- **Surat Tugas (`SuratTugas`)**:
  - Diproses penuh secara internal di SAKTI (tanpa perlu bolak-balik ke SRIKANDI).
  - Hak kelola (buat/edit) diberikan kepada Administrasi Tim Kreatif & `super_admin`; hapus khusus `super_admin`; pegawai non-admin hanya melihat surat tempat ia ditugaskan.
  - Penandatangan Eselon III ke atas (selain Kadis otomatis `a.n. Kepala Dinas`).
  - Saat disetujui/disahkan (`tandatanganiDiSakti()`), sistem menerbitkan nomor format `ST/0001/TK/2026`, menghitung hash SHA-256, menyematkan **QR Code berlogo Geopark** + keterangan *"Disetujui secara elektronik"* pada PDF, serta mengirim notifikasi ke pembuat, tim penerima tugas, dan penandatangan (khusus Tim Kreatif).
  - Lampiran otomatis berbentuk tabel bila jumlah petugas `> 4` orang.

### C. Presensi, Mesin ZKTeco, Izin/Cuti & Mobile PWA
- **Jendela Waktu Presensi (`JadwalKerja`)**:
  - Buka masuk `06:00`, jam masuk `07:30`, toleransi keterlambatan (default 15 menit), tutup masuk `09:00`, jam pulang Senin–Kamis `16:00` dan Jumat `16:30` (otomatis via `dayOfWeekIso === 5`), tutup pulang `23:59`.
  - Pegawai tidak bisa tap pulang jika belum tap masuk pagi hari (kecuali berstatus Dinas Luar / Izin resmi).
  - Pegawai `wfh` bebas geofencing; pegawai `wfo` wajib dalam radius koordinat kantor.
- **Mesin ZKTeco & Mobile PWA**: Sinkronisasi log mesin ke `LogAbsensi` dan rekap ke `AbsensiHarian`. Tersedia bulk action *"Set Jam Pulang Massal"* untuk pegawai lupa tap pulang.
- **Izin & Cuti (`PengajuanIzin`)**:
  - Persetujuan berjenjang (Atasan/Kabid ➔ Kadis) melalui tab **Verifikasi Bawahan** + notifikasi database; saat disetujui otomatis men-generate `AbsensiHarian` dan memotong kuota cuti.
  - **Imutabilitas**: Pengajuan berstatus `disetujui`/`ditolak` terkunci permanen (tidak dapat diubah/dihapus pegawai).
  - **Master Global Read-Only**: Data Jabatan, Jadwal Kerja, dan Hari Libur dapat dilihat seluruh pegawai, namun mutasi dibatasi hanya untuk Admin Kepegawaian & Superadmin.
  - Cetak PDF `surat-cuti.blade.php` (kategori cuti) dan `surat-izin.blade.php` (izin/sakit/DL/WFH).

### D. Laporan Harian Kinerja (LHK)
- **Syarat Pengisian**: Pegawai wajib memiliki `AbsensiHarian` lengkap (masuk & pulang) atau berstatus Dinas Luar pada tanggal tersebut.
- **Konten**: Terhubung ke `Aktivitas` & `RencanaAksi`, mencatat durasi menit, output/bukti, dan verifikasi atasan (`menunggu`, `disetujui`, `ditolak`).

### E. Keuangan, RKA & SPJ Terpadu
- **Penguncian Tahun Anggaran**: Global mengikuti `PengaturanAplikasi::getTahunAnggaranAktif()`, field `tahun_anggaran` read-only.
- **RKA & Pagu**: Item belanja menarik `harga_satuan` secara read-only dari `RincianAnggaranBelanja` agar tidak melampaui pagu.
- **Penomoran SPJ**: Format `SP/{singkatan_bidang}/{bulan}/{tahun}/{KODE_UNIK}` (otomatis & read-only).
- **Jenis Belanja**: `barang_jasa`, `makan_minum`, `sppd`, `bbm` (kuota liter mengacu pada `MasterTujuanBbm`), dengan metode `e_katalog` atau `non_katalog`.
- **Pajak Otomatis**: Belanja barang di atas Rp 2.000.000 otomatis dikenakan PPN 11% dan PPh 22 1,5%.
- **Alur Verifikasi**: Pemohon (Staf) ➔ PPTK (Kabid) ➔ PPK ➔ PA (Kadis) ➔ Pencairan (Bendahara), menghasilkan PDF Nota Pesanan, Kwitansi, dan NPD.
- **Pelaporan Eksekutif (LRFK & LPJ Fungsional Bendahara)**:
  - **Realisasi Keuangan**: Akumulasi SPJ dicairkan s/d bulan laporan dibagi Pagu DPA (`%`) dan dibandingkan Target ROPK (`Serapan %`).
  - **Realisasi Fisik Tertimbang**: `Bobot Item = Pagu Item / Total Pagu Kegiatan`, `Progres Item = min(100%, Volume SPJ Selesai / Pagu Volume RKA)`, dan `Realisasi Fisik Tertimbang = ∑(Bobot Item × % Progres Item)`.
  - **LPJ Fungsional**: Rekap realisasi per Kode Rekening Belanja, register SP2D/Kas, serta rekap pemotongan & penyetoran pajak (PPN, PPh 21/22/23).
