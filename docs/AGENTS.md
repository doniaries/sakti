# AGENTS.md — SOP & Aturan Koding AI (SAKTI)

Dokumen ini adalah SOP wajib bagi seluruh AI Agent dan pengembang saat mengubah atau menambah kode di repositori **SAKTI**.  
Sebelum bekerja, pahami juga konteks pada:
- [PRD.md](PRD.md) — Aturan bisnis & fitur
- [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) — Konsistensi UI, warna, & PDF
- [ARCHITECTURE.md](ARCHITECTURE.md) — Struktur folder & alur data

---

## 1. Stack & Versi Wajib
- **PHP 8.3+** | **Laravel 12/13** | **Filament 4.x/5.x** | **Livewire 3/4** | **MySQL 8.0+**
- **DILARANG** menghasilkan sintaks Filament 2 atau Filament 3.

---

## 2. Aturan Filament 4/5 (WAJIB)

### A. Pemisahan Namespace Layout (`Schemas`) vs Input (`Forms`)
- **WAJIB (`Filament\Schemas\Components\*`)**: `Section`, `Grid`, `Group`, `Tabs`, `Tabs\Tab`, `Wizard`, `Wizard\Step`, `EmbeddedTable`, `Utilities\Get`, `Utilities\Set`.
- **DILARANG**: `Filament\Forms\Components\Section`, `Grid`, `Group` (Fatal Error di Filament 4/5).
- **Input Fields (`Filament\Forms\Components\*`)**: `TextInput`, `Select`, `DatePicker`, `DateTimePicker`, `Textarea`, `FileUpload`, `Repeater`, `Toggle`, `Checkbox`, `CheckboxList`, `Radio`, `Hidden`.

### B. Signature Method & Struktur Resource
- Form: `public static function form(Schema $schema): Schema`
- Table: `public static function table(Table $table): Table`
- Pisahkan `Schemas/[Nama]Form.php`, `Tables/[NamaPlural]Table.php`, `Pages/`, dan `Widgets/`.
- Pada `CreateRecord` & `EditRecord`, arahkan redirect kembali ke index via `getRedirectUrl()`.

### C. Prinsip Native Murni & Anti-Over-Engineering
- Gunakan 100% komponen native Filament (`Schema`, `Table`, `Infolist`, `Action`, `HasTabs` + `EmbeddedTable`).
- **DILARANG** membuat Blade/HTML/Tailwind manual untuk halaman CRUD, form, atau tabel standar.
- Jangan membuat abstraksi, helper, atau perubahan file di luar cakupan permintaan user.

---

## 3. Standar Form, Tabel, & Lokalisasi Indonesia
- **Tanggal Form**: `DatePicker` wajib `->displayFormat('d/m/Y')`; `DateTimePicker` wajib `->displayFormat('d/m/Y H:i')`.
- **Tanggal Tabel**: `TextColumn` wajib `->date('d/m/Y')` atau `->dateTime('d/m/Y H:i')`.
- **Kolom Tabel**: Gunakan `->searchable()` dan `->sortable()` pada kolom relevan; gunakan `TextColumn`, `IconColumn`, `ImageColumn`, `ToggleColumn`.
- **Validasi**: Terapkan di form component (`->required()`, `->maxLength()`, `->unique(ignoreRecord: true)`), dan constraint database.

---

## 4. Query Optimization & Type Hinting (Anti Warning IDE)
- Gunakan `Model::query()->...` alih-alih static call dinamis langsung pada `Model::where(...)` bila berpotensi memicu warning Intelephense.
- Pada resource besar, gunakan `modifyQueryUsing()` dengan `with()`, `select()`, `withCount()`. Dilarang `Model::all()`.
- Pada closure `->options()`, selalu kembalikan `array` (`->pluck('nama', 'id')->all()`), bukan `Collection`.
- Setiap memakai `auth()->user()` atau `Auth::user()`, tambahkan anotasi:
  ```php
  /** @var \App\Models\User|null $user */
  $user = Auth::user();
  ```

---

## 5. Checklist Sebelum Selesai (MANDATORY)
1. Jalankan `php -l` pada seluruh file PHP yang dibuat atau diubah.
2. Jika ada perubahan migrasi, jalankan `php artisan migrate --force` dan verifikasi hasilnya.
3. Jalankan `php artisan optimize:clear` setelah perubahan konfigurasi, route, view, atau resource.
4. Commit perubahan dengan pesan yang jelas dan ringkas.
5. Laporkan jawaban akhir secara ringkas dengan format:
   - **Ringkasan Poin Perubahan**
   - **File yang Diubah**
   - **Command**
   - **Git Commit**
