# Pedoman Arsitektur & Pengembangan Livewire 3/4 (LIVEWIRE.md)

Dokumen ini adalah **pedoman baku dan standar teknis** untuk pengembangan antarmuka pengguna berbasis **Livewire 3/4** pada aplikasi **Absensi LHK Mobile PWA** dan ekstensi Filament.

---

## 1. Arsitektur & Peran Livewire (Terkoneksi Penuh ke Filament & API)

- **Sumber Data Tunggal (Single Source of Truth)**:
  - Seluruh data yang tampil pada klien Mobile PWA (`Dashboard`, `Absensi`, `Riwayat`, `LHK`, `Profil`) **100% bersumber dari tabel database Filament** (`absensi_harian`, `log_absensi`, `lhks`, `users`, `pengaturan_aplikasis`).
  - Dilarang membuat data fiktif atau hardcode pada komponen mobile.
- **Keselarasan dengan REST API**:
  - Endpoint API (`/api/absensi`, `/api/dashboard`, `/api/lhk`, `/api/me`) mengembalikan format JSON yang sinkron dengan data Filament Admin dan PWA Mobile.
  - Setiap perubahan status kehadiran, log jam masuk/pulang, sumber perangkat (`mesin`/`mobile`), maupun cetak PDF langsung mencerminkan data aktual yang terlihat di Panel Admin Filament.
- **Klien Web Mobile (PWA Pegawai)**: Terletak di `app/Livewire/Mobile/` dan `resources/views/livewire/mobile/`.
- **Target Pengguna**: Pegawai biasa dan pimpinan untuk presensi mobile berbasis web, rekapitulasi kehadiran, pengisian Laporan Harian Kinerja (LHK), dan pengelolaan profil mandiri.
- **Layout Utama**: `resources/views/components/layouts/mobile.blade.php`.
- **Perutean**: Menggunakan full-page components yang diregistrasikan di `routes/web.php` dengan awalan `/mobile`.

---

## 2. Navigasi & Tata Letak Mobile (Sticky Bottom Nav & Floating FAB)

Layout mobile mengadopsi standar modern aplikasi mobile (Glassmorphism & Floating Navigation Bar):

1. **Sticky Floating Bottom Navigation Bar**:
   - Menu navigasi bawah berposisi tetap (`position: fixed; bottom: 0`), memiliki efek *backdrop blur* dan rounded pill (`rounded-[28px]`).
   - Struktur Menu:
     1. **Beranda**: Mengarah ke `route('mobile.dashboard')`.
     2. **Riwayat**: Mengarah ke `route('mobile.riwayat')`.
     3. **Center Elevated Floating Action Button (FAB)**: Tombol bulat menonjol di tengah untuk **ABSEN** (`route('mobile.absensi')`). Tombol ini selalu muncul di setiap halaman dan mudah dijangkau dengan ibu jari.
     4. **LHK**: Mengarah ke `route('mobile.lhk.index')`.
     5. **Profil**: Mengarah ke `route('mobile.profil')` (Profil lengkap mandiri ala MyASN).
2. **SPA Navigasi**:
   - Seluruh link antarhalaman mobile wajib menyertakan atribut `wire:navigate` untuk transisi halaman instan tanpa reload penuh.
3. **Safe Area & Padding**:
   - Konten halaman dibungkus class `.content-area` dengan padding bawah minimal `pb-28` agar elemen formulir dan tombol aksi tidak tertutup oleh bottom bar terapung.

---

## 3. Logika & Aturan Bisnis Presensi Mobile

1. **Format Jam Server**:
   - Jam aktif server ditampilkan pada dashboard dan halaman absensi dengan format digital `HH:MM WIB` menggunakan pemisah titik dua `:` berkedip (blinking colon) setiap 1 detik tanpa menyertakan detik untuk tampilan yang ringkas dan tenang.
2. **Statistik Presensi Bulan Berjalan**:
   Dashboard menyajikan 8 kartu statistik kehadiran:
   - **Hadir**: Pegawai yang hadir tepat waktu atau terlambat.
   - **Terlambat**: Pegawai yang tap masuk lewat dari batas toleransi.
   - **Dinas Luar**: Pegawai dengan penugasan kedinasan luar kantor.
   - **Plg Cepat (Pulang Cepat)**: Pegawai yang tap keluar sebelum jam pulang resmi jadwalnya.
   - **Izin**: Pengajuan izin yang disetujui.
   - **Sakit**: Keterangan sakit.
   - **Alpa (Tanpa Keterangan)**: Tidak hadir dan tidak ada konfirmasi izin/DL.
   - **LHK**: Akumulasi total laporan kinerja yang sudah disubmit bulan ini.
3. **Validasi Wajib Absen Masuk Sebelum Absen Keluar**:
   - Sistem menolak pengambilan absen keluar/pulang jika pegawai belum mengambil absen masuk pada pagi hari.
   - Jika pegawai membuka halaman presensi pada sore hari tanpa riwayat masuk, sistem menampilkan banner peringatan merah: *"Anda tidak dapat mengambil absen pulang karena tidak mengambil absen masuk pagi ini. Status Anda hari ini tercatat Tidak Hadir / Alpa."*

---

## 4. Modul Profil Pegawai Mobile (Model Tab MyASN)

Halaman profil pegawai di mobile (`App\Livewire\Mobile\Profil`) mengadopsi struktur infolist MyASN yang bersih dan interaktif:

1. **5 Tab Navigasi Horizontal**:
   - **Data Utama**: NIP, NIK, Jenis Kelamin, Tempat/Tgl Lahir, Agama, No. HP/WA, Email Dinas, Email Pribadi, dan Alamat Domisili.
   - **Golongan**: Golongan/Ruang, Pangkat Pembina, Eselon, NPWP, BPJS Kesehatan, dan No. Taspen.
   - **Jabatan**: Nama Jabatan, Bidang Dinas, Atasan Langsung, Tipe Kerja (WFH/WFO), Shift Kerja, dan PIN ID Mesin.
   - **Pendidikan**: Jenjang Pendidikan (S3/S2/S1/D3/SLTA/SMP/SD), Jurusan/Prodi, Gelar Depan, dan Gelar Belakang.
   - **Keluarga**: Nomor KK, NIK, dan Alamat Domisili Keluarga.
2. **Fitur Edit Profil Mandiri**:
   - Pegawai dapat menekan tombol **"Edit Profil"** untuk memperbarui data kontak, alamat domisili, data administrasi kependudukan, mengganti foto profil (avatar upload), dan mengubah kata sandi mandiri.

---

## 5. Pedoman Sintaks & Standar Kode Livewire 3/4

Setiap komponen Livewire baru atau hasil refactor wajib mematuhi aturan berikut:

### A. Deklarasi Class & Properties
1. Gunakan PHP 8.2+ Typed Properties:
   ```php
   public string $search = '';
   public ?int $selectedId = null;
   public bool $isEditing = false;
   ```
2. Hindari menyimpan objek Query Builder atau Closure pada property publik Livewire (karena tidak dapat diserialisasi ke JSON). Simpan ID atau array data primitif.

### B. Binding & Direktif Template
1. Gunakan `wire:model.live` untuk pencarian/filter reaktif instan.
2. Gunakan `wire:model.blur` pada input formulir panjang untuk menghemat request jaringan HTTP.
3. DILARANG menggunakan `wire:model.lazy` (sintaks usang Livewire 2).

### C. Event Dispatching
- Pengiriman event dari komponen PHP:
  ```php
  $this->dispatch('notify', [
      'type' => 'success',
      'title' => 'Berhasil!',
      'message' => 'Data berhasil disimpan.',
  ]);
  ```
- DILARANG menggunakan `$this->emit()` (sintaks usang Livewire 2).

### D. Optimasi Performa & Render
1. Selalu tambahkan `wire:key` pada setiap perulangan `@foreach`:
   ```html
   @foreach ($riwayat as $item)
       <div wire:key="riwayat-{{ $item->id }}">...</div>
   @endforeach
   ```
2. Gunakan atribut layout modern:
   ```php
   #[Layout('components.layouts.mobile')]
   #[Title('Judul Halaman')]
   ```
3. Gunakan `#[Lazy]` untuk pemuatan komponen berat di latar belakang.
