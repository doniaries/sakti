# Pedoman & Aturan Pengembangan (AGENTS.md)

Dokumen ini adalah pedoman baku dan aturan wajib untuk AI Agent dan pengembang dalam memelihara dan mengembangkan proyek **Absensi LHK**.

---

## 1. Stack Teknologi & Versi

Aplikasi ini dibangun menggunakan arsitektur modern:
- **PHP**: 8.3+
- **Laravel Framework**: 12.x / 13.x
- **Backend Admin & Panel**: Filament 4.x / 5.x (Full Filament Native)
- **Database**: MySQL 8.0+
- **Autentikasi**: Laravel Sanctum (Mobile & API), Filament Shield (RBAC Panel)
- **Klien Mobile Pegawai**: Flutter App (`absensi_flutter`)

> **PENTING (WAJIB):** DILARANG menghasilkan kode berdasarkan asumsi Filament 2 atau Filament 3. Semua komponen, schema, table, dan method wajib menggunakan sintaks Filament 4/5 native. Seluruh backend admin dikelola penuh menggunakan Filament.

---

## 2. Struktur Proyek Filament 4/5

Struktur resource mengikuti konvensi Filament terbaru dengan pemisahan `Schemas/`, `Tables/`, `Pages/`, `Widgets/`, dan `Infolists/`:

```txt
app/
└── Filament/
    ├── Resources/
    │   └── [NamaPlural]s/
    │       ├── [Nama]Resource.php
    │       ├── Schemas/
    │       │   ├── [Nama]Form.php
    │       │   └── [Nama]Infolist.php
    │       ├── Tables/
    │       │   └── [NamaPlural]Table.php
    │       ├── Pages/
    │       │   ├── Create[Nama].php
    │       │   ├── Edit[Nama].php
    │       │   ├── List[NamaPlural].php
    │       │   └── View[Nama].php
    │       ├── RelationManagers/
    │       └── Widgets/
    ├── Pages/
    ├── Widgets/
    └── Clusters/
```

### Signature Method Wajib Filament 4/5
```php
public static function form(Schema $schema): Schema
public static function table(Table $table): Table
```

### Redirect URL Standar
Di halaman `CreateRecord` dan `EditRecord`:
```php
protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('index');
}
```

---

## 3. Aturan Form & Lokalisasi Tanggal (Indonesia)

1. **Komponen Native**: Gunakan komponen native Filament (`TextInput`, `Select`, `DatePicker`, `TimePicker`, `DateTimePicker`, `FileUpload`, `Toggle`, `Radio`, dll).
2. **Format Tanggal Indonesia (Wajib)**:
   - Form `DatePicker`: Selalu sertakan `->displayFormat('d/m/Y')`
   - Form `DateTimePicker`: Selalu sertakan `->displayFormat('d/m/Y H:i')`
   - Table `TextColumn`: Gunakan `->date('d/m/Y')` atau `->dateTime('d/m/Y H:i')`
   - Hindari format default bahasa Inggris seperti `d-M-Y` yang menampilkan singkatan bulan Inggris (e.g. `Oct`).
3. **Validasi Lengkap**: Validasi wajib diterapkan di form component (`->required()`, `->numeric()`, dsb.), Form Request / Closure rules, dan database constraint.

---

## 4. Aturan Table & Query Optimization

1. **Table Columns**:
   - Selalu gunakan `->searchable()` dan `->sortable()` pada kolom yang relevan.
   - Gunakan komponen siap pakai: `TextColumn`, `IconColumn`, `ImageColumn`, `BadgeColumn`, `ToggleColumn`.
   - Hindari render HTML manual di view table.
2. **Eager Loading & Anti N+1**:
   - Pada resource data besar, wajib gunakan `modifyQueryUsing()` dengan `with()`, `select()`, dan `withCount()`.
   - DILARANG menggunakan `Model::all()` pada resource atau halaman dengan volume data besar.

---

## 5. Domain & Alur Bisnis Aplikasi

1. **Master Kepegawaian & Organisasi**:
   - `Instansi`, `Bidang`, `Jabatan`, `User` (Pegawai).
   - Relasi atasan-bawahan via `atasan_id` pada model `User`.
   - **Aturan Pimpinan / Kadis**: Kepala Dinas merupakan pimpinan tertinggi di dinas sehingga:
     - `bidang_id` = `null` (tidak terikat bidang).
     - `atasan_id` = `null` (tidak memiliki atasan langsung di internal dinas).
     - Tab khusus di List Pegawai: "Pimpinan / Tanpa Bidang".
   - **Tipe Kerja Pegawai**:
     - `status_kerja`: `wfo` (Work From Office) atau `wfh` (Work From Home).
     - Pegawai `wfh` dapat melakukan absensi secara fleksibel (lokasi/koordinat bebas tanpa batasan geofencing kantor).
   - **Biodata Pegawai Lengkap**:
     - Jenjang Pendidikan (`S3`, `S2`, `S1`, `D3`, `SLTA`, `SMP`, `SD`), Jurusan, Gelar Depan & Belakang.
     - Nomor NPWP & Tanggal NPWP (`displayFormat('d/m/Y')`), Nomor Taspen, Agama, Alamat, No. Telp / HP, Email Pribadi.
2. **Mesin Presensi & Log Absensi**:
   - `MesinAbsensi` (ZKTeco IP/Port) dan `LogAbsensi` (tarik log mesin via UDP/TCP socket atau input mobile).
3. **Rekap Presensi Harian (`AbsensiHarian`)**:
   - Mengelompokkan log masuk & pulang pegawai per hari kerja.
   - Status: `hadir`, `terlambat`, `tidak_hadir`, `izin`, `sakit`, `dinas_luar`.
4. **Laporan Harian Kinerja (`Lhk` & `LhkPekerjaan`)**:
   - Syarat pembuatan LHK: Pegawai wajib memiliki absensi harian lengkap (masuk & pulang) pada tanggal bersangkutan.
   - Memuat detail pekerjaan harian yang terhubung ke `Aktivitas` & `RencanaAksi`.
   - Menghitung durasi menit, output bukti URL/foto, dan verifikasi atasan (`menunggu`, `disetujui`, `ditolak`).
5. **Penilaian Reputasi Pegawai (`Reputasi`)**:
   - Penilaian peer-to-peer antar rekan kerja (1 - 5 bintang) beserta tanggal penilaian format Indonesia dan catatan review.

---

## 6. Autentikasi, Query, & Type Hinting (Anti Warning Intelephense)

1. **Autentikasi User**:
   Gunakan `\Illuminate\Support\Facades\Auth::user()` atau sertakan anotasi PHPDoc agar IDE dan linter mengenali model `User`:
   ```php
   /** @var \App\Models\User|null $user */
   $user = auth()->user();
   ```
2. **Query Eloquent Builder**:
   - Hindari pemanggilan langsung method dinamis pada static Model jika Intelephense memberikan false positive "expected 1 argument".
   - Gunakan `Model::query()->count()`, `Model::query()->where('column', '=', $value)->get()`.
3. **Options Select Component**:
   - Hindari mengembalikan `Collection` langsung pada closure `->options()`. Selalu sertakan `->all()` atau `->toArray()` (contoh: `Model::pluck('nama', 'id')->all()`).

---

## 7. Standar Presensi, Shift Kerja, Toleransi & Pengisian LHK

### A. Jendela Waktu Presensi (Attendance Windows) & Jam Kerja Fleksibel
Parameter waktu absensi disimpan pada tabel `jadwal_kerjas` dan dapat diatur langsung oleh Admin lewat Filament:
1. **Buka Absen Masuk**: Default `06:00:00` WIB (jam paling awal sistem/mesin menerima tap masuk).
2. **Jam Masuk Resmi**: Default `07:30:00` WIB (Senin - Kamis & Jumat).
3. **Toleransi Keterlambatan**: Disimpan dalam kolom `toleransi_terlambat_menit` (misal 15 menit: rentang `07:30` s.d `07:45` masih dianggap tepat waktu / hadir).
4. **Tutup Absen Masuk**: Default `09:00:00` WIB. Di atas batas toleransi s.d pukul 09:00 dicatat sebagai status `terlambat`. Lewat pukul 09:00 jendela masuk ditutup.
5. **Mulai Buka Absen Pulang**: Sesuai jam pulang resmi hari tersebut (Senin–Kamis `16:00:00`, Jumat `16:30:00` atau konfigurasi dinas).
6. **Tutup Absen Pulang**: Default `21:00:00` WIB (batas akhir pengambilan absen pulang di malam hari).

### B. Otomatisasi Jam Pulang Khusus Hari Jumat
- Menggunakan `Carbon::parse($tanggal)->dayOfWeekIso === 5` (Jumat).
- Sistem otomatis memakai `jam_masuk_jumat` dan `jam_pulang_jumat` tanpa perlu campur tangan manual admin setiap minggunya.

### C. Dinas Luar (DL) & Izin Resmi
- Pegawai berstatus **Dinas Luar** (`jenis_kehadiran = 'dinas_luar'`) otomatis dihitung **Hadir Penuh**.
- Jam masuk dan pulang terisi otomatis mengikuti jam jadwal hari tersebut.
- **LHK Langsung Terbuka**: Pegawai dinas luar berhak langsung membuat dan mengisi Laporan Harian Kinerja (LHK).

### D. Penanganan "Lupa Absen Pulang" & Filter Monitoring Cepat (Admin)
Di Resource `AbsensiHarian`:
1. **Tab Filter Cepat**:
   - **Semua Data**: Menampilkan seluruh riwayat.
   - **Lengkap**: Pegawai yang sudah tap Masuk dan Pulang (LHK siap diverifikasi atasan).
   - **Belum Pulang**: Pegawai yang sudah Masuk tetapi belum tap Pulang.
   - **Dinas Luar / Izin**: Pegawai dengan surat tugas / izin resmi.
   - **Tidak Hadir / Alpa**: Pegawai yang tidak mengambil absen masuk maupun pulang.
2. **Bulk Action 1-Klik Sakti**:
   - Tombol **"Set Jam Pulang Massal"** pada tab *Belum Pulang*.
   - Admin dapat mencentang seluruh pegawai yang tertinggal absen pulang dan mengisinya dengan jam pulang resmi hari tersebut secara serentak dalam 1 detik.

### E. Penentuan Otomatis Pegawai Tidak Hadir (Alpa)
- Jika lewat batas tutup absen masuk (`09:00:00`) seorang pegawai belum tap masuk dan tidak memiliki pengajuan izin/dinas luar yang disetujui, status dicatat sebagai `tidak_hadir`.
- Pegawai berstatus `tidak_hadir` otomatis **terkunci dari pengisian LHK** pada hari tersebut.

---

## 8. Widget Stat Card & Table Filter Synchronization

Ketika card widget pada Filament digunakan sebagai filter instan:
1. **URL Generator Widget**:
   Sertakan parameter query eksplisit selain `tableFilters`:
   ```php
   Stat::make('Pendidikan S1', $s1Count)
       ->url(UserResource::getUrl('index', [
           'tableFilters' => ['jenjang_pendidikan' => ['value' => 'S1']],
           'jenjang' => 'S1',
       ]));
   ```
2. **Table Query Hook**:
   Pastikan `configure(Table $table)` memiliki handler parameter query pada `modifyQueryUsing()`:
   ```php
   $table->modifyQueryUsing(function (Builder $query) {
       if ($jenjang = request()->query('jenjang')) {
           $query->where('jenjang_pendidikan', '=', $jenjang);
       }
   });
   ```

---

## 9. Pedoman Antarmuka Livewire 3/4 & Mobile PWA

Panduan arsitektur dan standar refactor komponen Livewire telah dipisahkan ke dalam dokumen tersendiri:
👉 **Silakan merujuk ke [`LIVEWIRE.md`](file:///c:/laragon/www/absensilhk/LIVEWIRE.md)** untuk pedoman teknis lengkap.

### Ringkasan Fitur Mobile Terkini:
1. **Navigasi Bawah Mengambang (Sticky Floating Bottom Nav)**:
   - Terdiri dari 5 menu: Beranda, Riwayat, **Tombol ABSEN melayang di tengah (FAB)**, LHK, dan Profil.
2. **Jam Digital Real-Time**: Format `HH:MM WIB` dengan titik dua berkedip (blinking colon) tanpa detik.
3. **Statistik 8-Card Kehadiran**: Hadir, Terlambat, Dinas Luar, Pulang Cepat, Izin, Sakit, Alpa, dan LHK Dibuat.
4. **Validasi Presensi**: Pegawai wajib memiliki absen masuk pagi sebelum dapat mengambil absen pulang di sore hari.
5. **Profil Pegawai Ala MyASN**: Tab Data Utama, Golongan, Jabatan, Pendidikan, dan Keluarga dengan kemampuan edit profil mandiri.

---

## 10. Arsitektur Portal Masuk, Autentikasi Peran & Pemisahan Akses (Admin vs Pegawai/Flutter)

Aplikasi memiliki dua ranah antarmuka utama yang dipisahkan secara tegas berdasarkan peran pengguna (*Role-Based Access Control*):

### A. Halaman Depan Portal Utama (`/`)
- Halaman root `/` menampilkan **Portal Gerbang Masuk** terpadu (`resources/views/welcome.blade.php`).
- Menyajikan dua pilihan akses utama:
  1. **Tombol Masuk Pegawai (Presensi & LHK Mobile PWA)**: Mengarah ke `route('mobile.login')` (atau jika sudah terautentikasi langsung ke `route('mobile.dashboard')`).
  2. **Tombol Masuk Administrator (Panel Filament Admin)**: Mengarah ke `/admin/login` (atau jika sudah terautentikasi langsung ke `/admin`).
- Menampilkan identitas dinas/instansi dari model `PengaturanAplikasi`, jam digital real-time, status user aktif (jika sudah login), dan tombol keluar/ganti akun.

### B. Proteksi Akses Panel Filament Admin (`canAccessPanel`)
- Model `User` mengimplementasikan interface `Filament\Models\Contracts\FilamentUser`.
- Method `canAccessPanel(Panel $panel): bool` memvalidasi hak akses:
  ```php
  public function canAccessPanel(Panel $panel): bool
  {
      return $this->hasAnyRole(['super_admin', 'admin']);
  }
  ```
- **Aturan Keamanan (PENTING)**: Pegawai biasa (hanya memiliki role `pegawai`) yang mencoba mengakses `/admin` atau login via panel admin akan ditolak otomatis oleh sistem (**HTTP 403 Forbidden**).

### C. Alur Pengguna Administrator (Akses Ganda & Switcher Cepat)
- Administrator sistem (`super_admin` dan `admin`) pada umumnya juga merupakan pegawai dinas yang wajib diabsen dan mengisi LHK.
- Oleh karena itu, user admin juga memiliki role `pegawai`.
- **Navigasi Bolak-Balik Terintegrasi**:
  1. **Dari Filament ke Mobile**: Di menu avatar Filament (pojok kanan atas), terdapat menu item **"Presensi Mobile Pegawai"** (`route('mobile.dashboard')`).
  2. **Dari Mobile ke Filament**: Pada dashboard Mobile PWA (`livewire.mobile.dashboard`), jika user memiliki role `admin`/`super_admin`, tersedia tombol pintas badge **"Admin"** (`/admin`) di samping tombol logout.

### D. Pedoman Bagi Klien Mobile Flutter (`absensi_flutter`)
Untuk pengembangan klien mobile Flutter:
1. **Autentikasi API**: Menggunakan Laravel Sanctum melalui endpoint `POST /api/login`.
2. **Validasi Role pada Response Login**:
   - Endpoint login API mengembalikan data profil pengguna beserta array `roles`.
   - Klien Flutter ditujukan **khusus untuk operasional Pegawai** (Presensi GPS/Geofencing, Log Kehadiran, Submit LHK, & Profil).
   - Pengguna dengan role `pegawai`, `admin`, maupun `super_admin` dapat menggunakan aplikasi Flutter untuk melakukan presensi harian.
   - Manajemen sistem (CRUD master data, tarik log mesin, manajemen user, dsb.) **TIDAK** diimplementasikan di Flutter, melainkan diarahkan untuk diakses via Filament Admin Panel di browser desktop/laptop.

---

## 11. Format Respon AI (MANDATORY / WAJIB)

Setiap kali AI memberikan solusi kode kepada pengguna, AI **WAJIB** menyertakan struktur berikut secara utuh:

### File
```txt
app/...
```

### Kode Final
Kode siap pakai.

### Command
```bash
php artisan optimize:clear
```
*(Tambahkan `php artisan migrate`, `php artisan make:...` bila ada penambahan file/skema)*

### Migration
```txt
Ya / Tidak
```

### Dampak
#### Backend
- Penjelasan dampak pada sistem backend...
#### Frontend
- Penjelasan dampak pada tampilan antarmuka...

### Git Commit (WAJIB)
```bash
git add .
git commit -m "type(scope): deskripsi perubahan"
```
*(Dilarang mengakhiri jawaban tanpa bagian Git Commit)*
