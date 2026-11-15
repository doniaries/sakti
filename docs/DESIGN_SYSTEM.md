# DESIGN SYSTEM — SAKTI

Dokumen ini menjadi acuan baku konsistensi visual, warna, komponen, dan tata letak UI pada Panel Admin Filament, Mobile PWA, serta Dokumen PDF resmi SAKTI.

---

## 1. Prinsip Desain Utama
1. **100% Filament Native Murni**: Gunakan primitif bawaan Filament 4/5 (`Schema`, `Section`, `Grid`, `Tabs`, `Table`, `Infolist`, `Action`) tanpa menulis Blade/HTML/Tailwind manual untuk form atau tabel standar.
2. **Anti-Slop UI**: Ringkas, lega, tidak padat teks, tidak ada kartu berlebihan atau info redundan.
3. **Konsistensi Bahasa & Format**: Seluruh label, placeholder, helper text, notifikasi, dan format tanggal menggunakan Bahasa Indonesia baku.

---

## 2. Palet Warna Resmi (Panel Filament)

Dikonfigurasi terpusat pada `app/Providers/Filament/AdminPanelProvider.php`:

| Token | Warna Filament | Penggunaan Baku |
| :--- | :--- | :--- |
| `primary` | `Color::Amber` | Aksi utama, tombol simpan, highlight aktif, nomor dokumen/surat. |
| `success` | `Color::Green` | Status disetujui/terbit/resmi TTE, hadir tepat waktu, selesai/cair. |
| `warning` | `Color::Orange` | Status draft, menunggu verifikasi, terlambat, catatan perhatian. |
| `danger` | `Color::Red` | Status ditolak, alpa/tidak hadir, aksi hapus, badge notif belum dibaca. |
| `info` | `Color::Blue` | Informasi netral, jumlah tim/pegawai, preview dokumen, dinas luar. |
| `gray` | Default Gray | Label sekunder, administrasi, kode/singkatan netral. |

Warna aksen tambahan yang terdaftar untuk badge kategori (`Bidang::badgeColor()` & status):
`purple`, `teal`, `rose`, `indigo`, `cyan`, `emerald`, `violet`, `lime`.

---

## 3. Standar Warna Badge Domain

### A. Badge Bidang (`Bidang::badgeColor()`)
- **Sekretariat**: `warning`
- **Pemasaran / Pariwisata**: `purple` / dinamis dari palet aksen
- **Destinasi / Ekonomi Kreatif**: `teal` / dinamis dari palet aksen
- **Pemuda & Olahraga**: `info`

### B. Badge Jabatan Tim Kreatif (`JabatanTimKreatif::getColor()`)
- **Pembina**: `danger`
- **Pengarah & PJ Bidang**: `warning`
- **Koordinator Tim Kreatif & Koordinator Teknis**: `primary`
- **Koordinator Bidang Teknis (Website, Desain, Dokumentasi, Medsos)**: `info`
- **Pelaksana Teknis, Penerjemah & Pengelola Geopark**: `success`
- **Administrasi**: `gray`

---

## 4. Tipografi & Lokalisasi Data

- **Format Tanggal Form**: `DatePicker::displayFormat('d/m/Y')`, `DateTimePicker::displayFormat('d/m/Y H:i')`.
- **Format Tanggal Tabel**: `TextColumn::date('d/m/Y')` atau `->dateTime('d/m/Y H:i')`. Dilarang memakai format bulan Inggris (`d-M-Y`).
- **Format Mata Uang**: Rupiah (`Rp 1.000.000`) menggunakan `FormatHelper::rupiahInput()` / `->money('IDR', locale: 'id')`.
- **Kolom Beku (Sticky Columns)**: Tabel lebar (Pegawai, Absensi Harian, LHK, Pengajuan SPJ) mengunci kolom identitas utama di sisi kiri dengan `->sticky()` dan `->stickyableColumns()`.

---

## 5. Standar Dashboard & Kartu Ringkas
- Kartu pada Dashboard hanya menampilkan informasi inti yang langsung dipakai pegawai/pimpinan (hindari kartu promosi/link eksternal yang tidak relevan).
- Setiap kartu statistik menggunakan latar belakang berwarna lembut/aksen yang jelas untuk membedakan kategori tanpa membuat teks sulit dibaca.
- Kartu widget yang berfungsi sebagai pintasan filter tabel wajib menyertakan parameter query sinkron (`tableFilters` + parameter eksplisit).

---

## 6. Standar Mobile PWA (`/mobile`)
- **Layout**: `resources/views/components/layouts/mobile.blade.php` dengan class konten `.content-area` (`pb-28` minimum).
- **Navigasi Bawah**: Sticky Floating Bottom Bar (`rounded-[28px]`, efek glassmorphism) berisi 5 menu: `Beranda`, `Riwayat`, **FAB Tengah (`ABSEN`)**, `LHK`, `Profil`.
- **Transisi SPA**: Seluruh tautan navigasi internal wajib memakai `wire:navigate`.
- **Jam Server**: Format `HH:MM WIB` dengan titik dua berkedip setiap 1 detik (tanpa detik).

---

## 7. Standar Dokumen PDF Resmi (DomPDF)
- **Kop Surat**: Logo resmi Kabupaten Sijunjung/Instansi rata kiri, nama Pemkab & Disparpora rata tengah tebal, garis pemisah ganda.
- **Surat Tugas Elektronik**:
  - Nomor surat resmi: `ST/XXXX/TK/TAHUN` (rata tengah di bawah judul **SURAT TUGAS**).
  - Pengesahan TTE SAKTI menampilkan **QR Code berlogo Geopark** dan teks miring *"Disetujui secara elektronik"* tepat di atas nama penandatangan.
  - Petugas `<= 4` tampil di badan surat; `> 4` otomatis pindah ke halaman Lampiran tabel.
