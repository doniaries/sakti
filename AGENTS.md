# Pedoman & Aturan Pengembangan (AGENTS.md)

Dokumentasi proyek **SAKTI (Sistem Administrasi Kinerja Terintegrasi)** tersusun rapi dalam 4 dokumen utama di folder `docs/`:

1. [docs/PRD.md](docs/PRD.md) — Bisnis proses, aktor/role, dan spesifikasi seluruh fitur & modul.
2. [docs/DESIGN_SYSTEM.md](docs/DESIGN_SYSTEM.md) — Standar UI, palet warna resmi Filament, badge, Mobile PWA, dan format PDF.
3. [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) — Stack teknologi, struktur folder, dan alur data sistem.
4. [docs/AGENTS.md](docs/AGENTS.md) — SOP lengkap aturan koding AI Agent.

---

## Ringkasan Aturan Wajib (Non-Negotiable)

- **Stack**: PHP 8.3+, Laravel 12/13, Filament 4.x/5.x, Livewire 3/4, MySQL 8.0+. **DILARANG** menghasilkan sintaks Filament 2/3.
- **Namespace Filament 4/5**:
  - Layout (`Section`, `Grid`, `Group`, `Tabs`, `Wizard`, `EmbeddedTable`, `Get`, `Set`) wajib dari `Filament\Schemas\Components\*`.
  - Input (`TextInput`, `Select`, `DatePicker`, `Textarea`, `FileUpload`, `Repeater`, `Toggle`, dll.) dari `Filament\Forms\Components\*`.
- **Signature Resource**: `form(Schema $schema): Schema` dan `table(Table $table): Table`, dengan pemisahan `Schemas/`, `Tables/`, `Pages/`, `Widgets/`.
- **Native Murni & Anti-Over-Engineering**: Gunakan komponen native Filament; jangan buat Blade/HTML/CSS custom untuk CRUD atau tabel standar.
- **Lokalisasi Indonesia**: `DatePicker::displayFormat('d/m/Y')`, `DateTimePicker::displayFormat('d/m/Y H:i')`, `TextColumn::date('d/m/Y')`.
- **Query & Type Hinting**: Gunakan `Model::query()`, `modifyQueryUsing()` dengan eager loading, `->all()` pada `Select::options()`, serta anotasi `/** @var \App\Models\User|null $user */` saat memakai `Auth::user()`.
- **Verifikasi & Respon**: Jalankan `php -l` dan `php artisan optimize:clear`, commit perubahan, lalu jawab ringkas (Ringkasan Poin Perubahan, File yang Diubah, Command, Git Commit).
