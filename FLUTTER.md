# Pedoman Arsitektur Flutter & Backend API (FLUTTER.md)

Dokumen ini adalah **pedoman baku dan kontrak teknis** untuk pengembangan aplikasi mobile **Flutter (`absensi_flutter`)** yang terhubung langsung dengan backend **Laravel + Filament 4/5**.

---

## 1. Arsitektur Sistem & Pembagian Peran

Aplikasi ini menggunakan model arsitektur terpisah yang bersih:
- **Backend Admin & Monitoring**: Murni menggunakan **Filament 4.x/5.x Native** untuk Admin, Pimpinan, dan Kepegawaian.
- **Klien Mobile Pegawai**: Dibangun menggunakan **Flutter** (Dart 3+) untuk presensi mobile harian, GPS geofencing, kamera selfie/liveness, pengajuan izin, dan pengisian Laporan Harian Kinerja (LHK).
- **Penghubung (Bridge)**: **Laravel REST API + Laravel Sanctum** dengan respons terstandar JSON.

---

## 2. Kontrak Autentikasi & Header API (Sanctum)

### A. Format Request Standar
Setiap request dari Flutter ke backend Laravel wajib menyertakan header berikut:
```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <sanctum_token>
```

### B. Endpoint Autentikasi
1. **Login**: `POST /api/login`
   - Payload: `{ "email_or_nip": "...", "password": "...", "device_name": "..." }`
   - Response: Token Sanctum + Data Profil Pegawai + Status Jadwal Kerja Hari Ini.
2. **Profil User (Me)**: `GET /api/me`
3. **Logout**: `POST /api/logout` (Menghapus token sesi aktif).

---

## 3. Modul Presensi Mobile (Absensi Flutter)

### A. Parameter Waktu & Validasi Jam Kerja
Backend menyediakan data jendela waktu dari `jadwal_kerjas` yang wajib dipatuhi aplikasi Flutter:
1. **Jendela Masuk**: `buka_absen_masuk` (06:00) s.d `tutup_absen_masuk` (09:00).
2. **Toleransi Keterlambatan**: `toleransi_terlambat_menit` (misal 15 menit dari jam 07:30).
3. **Jendela Pulang**: Mulai `jam_pulang` (Senin-Kamis `16:00`, Jumat `16:30`) s.d `tutup_absen_pulang` (21:00).
4. **Validasi Geofencing**: Validasi radius koordinat kantor (`latitude`, `longitude`, `radius_meter`). Flutter mengirimkan `latitude`, `longitude`, dan file foto `multipart/form-data`.

### B. Endpoint Presensi Mobile
- **Status Hari Ini**: `GET /api/absensi/today`
  - Mengembalikan data jam masuk, jam pulang, tipe (`WFO`/`WFH`/`DINAS LUAR`), dan kelayakan pengisian LHK.
- **Kirim Tap Absen**: `POST /api/absensi`
  - Parameter: `tipe` (`masuk` / `pulang`), `latitude`, `longitude`, `foto` (Selfie File).
- **Riwayat Absensi**: `GET /api/absensi?bulan=X&tahun=Y`

---

## 4. Modul Laporan Harian Kinerja (LHK)

### A. Aturan Bisnis LHK pada Flutter
1. **Syarat Pengisian**:
   - Pegawai **wajib memiliki absensi masuk & pulang** pada tanggal bersangkutan, **ATAU**
   - Pegawai berstatus **Dinas Luar (DL)** yang sudah disetujui.
2. **Pegawai Alpa/Tidak Hadir**:
   - Jika pegawai tidak memiliki jam masuk/pulang lewat batas hari, tombol pengisian LHK pada Flutter dinonaktifkan (*disabled*) dengan keterangan: *"Anda tidak memiliki presensi lengkap pada tanggal ini"*.
3. **Validasi Wajib Absen Masuk**:
   - Flutter wajib memvalidasi pengambilan absen keluar: jika pegawai belum memiliki catatan absen masuk pagi hari, sistem Flutter tidak mengizinkan pengiriman request absen keluar dan menampilkan pesan peringatan.

### B. Endpoint LHK & Profil Pegawai
- **Daftar Aktivitas SKP**: `GET /api/lhk/aktivitas` (Mengambil master aktivitas, uraian tugas, dan menit acuan).
- **Cek Status Tanggal**: `GET /api/lhk/absensi-tanggal?tanggal=YYYY-MM-DD`
- **Kirim LHK Harian**: `POST /api/lhk`
  - Payload: Tanggal, array pekerjaan (`aktivitas_id`, `jam_mulai`, `jam_selesai`, `lama_menit`, `hasil_pekerjaan`, `bukti_url`).
- **Daftar LHK Pegawai**: `GET /api/lhk`
- **Detail LHK**: `GET /api/lhk/{id}`
- **Update Profil Pegawai**: `POST /api/me/update` (Menyimpan perubahan data kontak, alamat, biodata MyASN, dan foto profil).

---

## 5. Standar Format Respons API (JSend / Standard JSON)

Backend selalu mengirimkan format seragam yang diparsing oleh Flutter Model:

```json
{
  "success": true,
  "message": "Presensi masuk berhasil dicatat.",
  "data": {
    "id": 120,
    "tanggal": "2026-10-22",
    "jam_masuk": "07:25:00",
    "status": "hadir",
    "jenis_kehadiran": "wfo"
  }
}
```

Format respons error:
```json
{
  "success": false,
  "message": "Anda berada di luar radius kantor (Jarak: 350m, Maks: 100m).",
  "errors": {
    "koordinat": ["Di luar radius absensi."]
  }
}
```

---

## 6. Standar State Management & Arsitektur Flutter

1. **Struktur Folder Flutter**:
   ```txt
   lib/
   ├── core/
   │   ├── constants/       <-- Base URL, API Endpoints, Theme Colors
   │   ├── network/         <-- Dio / Http Client Interceptor (Auth Token, Error Handler)
   │   └── utils/           <-- Date formatter Indonesia, Location Helper
   ├── data/
   │   ├── models/          <-- User, AbsensiHarian, Lhk, Aktivitas
   │   └── repositories/    <-- AbsensiRepository, LhkRepository, AuthRepository
   └── presentation/
       ├── bloc/ or provider/ or riverpod/
       ├── pages/           <-- Login, Dashboard, KameraPresensi, Riwayat, FormLhk
       └── widgets/         <-- CustomButton, CardPresensi, LocationChecker
   ```
## 7. Keamanan Presensi & Anti-Fraud (Anti Fake GPS, Mock Location & Root Detection)

Demi menjaga integritas dan keaslian kehadiran pegawai, klien Flutter dan Backend Laravel menerapkan proteksi ketat anti-manipulasi lokasi:

### A. Deteksi Mock Location & Fake GPS di Flutter
1. **Pemeriksaan Mock Location Native**:
   - Gunakan `geolocator`: periksa `Position.isMocked`. Jika `true`, sistem **WAJIB** menolak absensi seketika dengan pesan: *"Terdeteksi penggunaan Lokasi Palsu (Mock Location/Fake GPS). Mohon nonaktifkan aplikasi fake GPS!"*.
   - Gunakan paket keamanan lokasi tambahan seperti `trust_location` atau `safe_device` untuk mendeteksi apakah *Developer Options / Mock Location App* sedang aktif.
2. **Integritas Waktu Perangkat**:
   - Waktu jam absen **wajib mengacu pada server backend**, bukan jam lokal HP pegawai (untuk mencegah pegawai memundurkan jam sistem di HP mereka).
3. **Deteksi Root & Jailbreak**:
   - Gunakan plugin `flutter_jailbreak_detection` / `safe_device`.
   - Jika perangkat terdeteksi di-root (Magisk, KernelSU, SuperSU) atau di-jailbreak, munculkan peringatan keamanan atau blokir fitur tap presensi.
4. **Deteksi Emulator**:
   - Cegah presensi dijalankan melalui emulator Android di PC (seperti Nox, BlueStacks, LDPlayer) menggunakan pemeriksaan `SafeDevice.isRealDevice`. Presensi hanya diizinkan di ponsel fisik asli.

### B. Parameter Payload Presensi Tambahan
Setiap pengiriman absen (`POST /api/absensi`), Flutter wajib menyertakan verifikasi hardware:
```json
{
  "tipe": "masuk",
  "latitude": -8.123456,
  "longitude": 115.123456,
  "accuracy": 12.5,
  "is_mocked": false,
  "is_real_device": true,
  "device_id": "ABC123XYZ",
  "device_model": "Samsung Galaxy A54"
}
```

### C. Verifikasi Keamanan di Backend Laravel
1. Jika `is_mocked === true` atau akurasi GPS tidak wajar (misal `accuracy > 100` meter), tolak request dengan kode `422 Unprocessable Entity`.
2. Pegawai dengan tipe kerja `wfo` wajib dicek jarak koordinatnya terhadap `latitude` dan `longitude` kantor pada radius toleransi `radius_meter` (Haversine Formula).
3. Pegawai dengan status kerja `wfh` diperbolehkan absen di luar radius kantor, namun proteksi **Anti Mock / Fake GPS** tetap aktif 100%.
