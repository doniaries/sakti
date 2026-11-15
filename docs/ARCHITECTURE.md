# ARCHITECTURE — SAKTI

Dokumen ini menjelaskan struktur direktori, stack teknologi, arsitektur data, serta alur integrasi antar-lapisan pada aplikasi SAKTI.

---

## 1. Stack Teknologi
- **Bahasa & Runtime**: PHP 8.3+
- **Framework**: Laravel 12.x / 13.x
- **Admin Panel**: Filament 4.x / 5.x (Full Declarative Schema & Table API)
- **Frontend Interaktif**: Livewire 3/4 + Alpine.js + Tailwind CSS (Vite)
- **Database**: MySQL 8.0+
- **Autentikasi & RBAC**: Session Web (Panel & PWA), Laravel Sanctum (API Mobile), Spatie Permission + Filament Shield
- **PDF & QR**: `barryvdh/laravel-dompdf`, `endroid/qr-code` (`App\Support\QrCodeHelper`)
- **Klien Eksternal**: Flutter App (`absensi_flutter`)

---

## 2. Struktur Direktori Utama

```text
app/
├── Enums/                      # Enum domain (JabatanTimKreatif, Eselon, dll.)
├── Filament/
│   ├── MenuBuilder/            # SidebarNavigationService, ResourceItemType, CustomMenuResource
│   ├── Pages/                  # Halaman khusus (Dashboard, TimKreatif, LaporanFungsional, LaporanLrfk, dll.)
│   ├── Resources/              # Resource Filament 4/5 (Pages/, Schemas/, Tables/, RelationManagers/, Widgets/)
│   └── Widgets/                # Widget KPI & statistik dashboard
├── Http/
│   └── Controllers/            # Print PDF controllers (SuratTugasPrintController, SpjPrintController, CetakLaporanController, DokumenTrackingController) & API
├── Livewire/
│   └── Mobile/                 # Komponen PWA Pegawai (Dashboard, Absensi, Riwayat, Lhk, Profil)
├── Models/                     # Eloquent Models & Relasi
├── Observers/                  # Observer sinkronisasi & notifikasi (PengajuanSpjObserver, dll.)
├── Policies/                   # Kebijakan akses Filament Shield
├── Providers/Filament/         # AdminPanelProvider.php (konfigurasi panel, warna, plugin, auth guard)
├── Services/                   # Business logic terpisah (LaporanKeuanganCalculationService, DatabaseBackupService, ActivityLogger)
└── Support/                    # Helper utilitas (QrCodeHelper, FormatHelper)

database/
├── migrations/                 # Skema database & migrasi data otomatis (termasuk Menu Builder & backfill)
└── seeders/                    # Seeder master & struktur (BidangSeeder, TimKreatifSeeder, MenuSeeder, dll.)

resources/
├── css/filament/admin/         # Custom Vite theme Filament (termasuk sticky columns)
└── views/
    ├── filament/pages/         # Wrapper view minimal halaman Filament
    ├── livewire/mobile/        # Tampilan Mobile PWA
    └── pdf/                    # Template DomPDF (surat-tugas, spj-dokumen, surat-izin, surat-cuti, rka-dokumen, laporan)
```

---

## 3. Arsitektur Alur Data (Single Source of Truth)

```text
                   ┌──────────────────────────┐
                   │      MySQL Database      │
                   │  (Single Source Truth)   │
                   └────────────┬─────────────┘
          ┌─────────────────────┼─────────────────────┐
          ▼                     ▼                     ▼
┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│  Filament Admin  │  │  Mobile PWA      │  │  REST API        │
│  (/admin)        │  │  (/mobile)       │  │  (/api/*)        │
│  Livewire + PHP  │  │  Livewire SPA    │  │  Sanctum + App   │
└────────┬─────────┘  └──────────────────┘  └──────────────────┘
         │
         ├──► SidebarNavigationService (menus + menu_items DB ➔ Sidebar)
         ├──► Print Controllers ➔ DomPDF + QrCodeHelper (Verifikasi Publik)
         └──► Database Notifications (SuratTugas, PengajuanSpj, Izin)
```

---

## 4. Mekanisme Penting Sistem

### A. Navigasi Dinamis (Menu Builder)
- `AdminPanelProvider` memanggil `SidebarNavigationService::build($builder)` untuk merakit sidebar dari tabel `menus`, `menu_locations`, dan `menu_items`.
- Setiap item URL dipetakan ke class Resource/Page melalui `ResourceItemType::getCatalog()` dan dicek visibilitasnya via `canAccess()` / `canViewAny()`.
- Perubahan struktur menu permanen dilakukan via migration/seeder agar otomatis sinkron di database tanpa input manual berulang.

### B. Sinkronisasi Instansi & Pengaturan Aplikasi
- `Instansi` (data kedinasan resmi) dan `PengaturanAplikasi` (parameter teknis GPS/radius/tahun anggaran) saling menyinkronkan field bersama saat `saved` agar modul lama tetap kompatibel 100%.

### C. Keamanan & Switch Akun
- `FilamentDeveloperLoginsPlugin` (`Switch to` di topbar) dibatasi pada `AdminPanelProvider`: aktif bebas di lingkungan `local`, dan hanya aktif untuk `super_admin` di mode server/production.

### D. Verifikasi Dokumen Elektronik (QR Tracking)
- `SuratTugas` dan `PengajuanSpj` menghasilkan URL verifikasi publik yang dikodekan ke QR Code berlogo Geopark via `QrCodeHelper::generateWithGeoparkLogo()`, lalu divalidasi oleh `DokumenTrackingController` / route `surat-tugas.verifikasi`.
