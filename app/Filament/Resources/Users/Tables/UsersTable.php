<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use STS\FilamentImpersonate\Actions\Impersonate;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (\Illuminate\Database\Eloquent\Builder $query) {
                /** @var \App\Models\User|null $user */
                $user = \Illuminate\Support\Facades\Auth::user();

                // Selalu kecualikan akun teknis Super Admin murni dari daftar pegawai dinas
                $query->bukanSuperAdmin();

                if ($user && ! $user->canViewAllBidangKepegawaian()) {
                    $userBidangId = (int) ($user->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $user->id)->value('id') ?? 0));
                    $query->where('bidang_id', $userBidangId);
                }

                if ($jenjang = request()->query('jenjang')) {
                    $query->where('jenjang_pendidikan', '=', $jenjang);
                }

                $query->with(['bidang:id,nama', 'jabatan:id,nama', 'roles:id,name']);
            })
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([5, 10, 25, 50, 100])
            ->stickyableColumns()
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('avatar_url')
                    ->disk('public')
                    ->label('Avatar')
                    ->circular(),
                /* ->sticky(), */

                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->formatStateUsing(fn(?string $state): string => mb_strtoupper((string) $state, 'UTF-8'))
                    ->description(fn($record): ?string => filled($record->nip) ? 'NIP. ' . $record->nip : null)
                    ->url(fn($record) => \App\Filament\Resources\Users\UserResource::getUrl('view', ['record' => $record]))
                    ->sticky(),

                \Filament\Tables\Columns\TextColumn::make('email')
                    ->label('Email User')
                    ->searchable(['email', 'email_resmi'])
                    ->sortable()
                    ->color('info')
                    ->icon('heroicon-m-pencil-square')
                    ->description(fn($record): ?string => filled($record->email_resmi) ? 'Resmi: ' . $record->email_resmi : 'Email resmi: -')
                    ->tooltip('Klik untuk mengubah email & password pegawai')
                    ->action(
                        Action::make('ubah_email_cepat')
                            ->label('Ubah Email & Password')
                            ->modalHeading(fn(\App\Models\User $record): string => "Ubah Email & Password: {$record->name}")
                            ->modalWidth('md')
                            ->modalSubmitActionLabel('Simpan Perubahan')
                            ->visible(fn(): bool => Auth::user()?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian']) ?? false)
                            ->fillForm(fn(\App\Models\User $record): array => [
                                'email' => $record->email,
                                'email_resmi' => $record->email_resmi,
                            ])
                            ->form([
                                \Filament\Forms\Components\TextInput::make('email')
                                    ->label('Email Login')
                                    ->email()
                                    ->required()
                                    ->unique(
                                        table: 'users',
                                        column: 'email',
                                        ignorable: fn(\App\Models\User $record): \App\Models\User => $record,
                                    )
                                    ->maxLength(255),
                                \Filament\Forms\Components\TextInput::make('email_resmi')
                                    ->label('Email Resmi (@sijunjung.go.id)')
                                    ->email()
                                    ->placeholder('Contoh: doniaries@sijunjung.go.id')
                                    ->unique(
                                        table: 'users',
                                        column: 'email_resmi',
                                        ignorable: fn(\App\Models\User $record): \App\Models\User => $record,
                                    )
                                    ->maxLength(255),
                                \Filament\Forms\Components\TextInput::make('password')
                                    ->label('Password Baru (Opsional)')
                                    ->password()
                                    ->revealable()
                                    ->placeholder('Kosongkan jika tidak mengubah password...')
                                    ->rules(['nullable', 'min:6'])
                                    ->maxLength(255),
                                \Filament\Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Konfirmasi Password Baru')
                                    ->password()
                                    ->revealable()
                                    ->placeholder('Ulangi password baru...')
                                    ->requiredWith('password')
                                    ->same('password')
                                    ->maxLength(255),
                            ])
                            ->action(function (\App\Models\User $record, array $data): void {
                                $payload = [
                                    'email' => strtolower(trim((string) $data['email'])),
                                    'email_resmi' => filled($data['email_resmi'] ?? null)
                                        ? strtolower(trim((string) $data['email_resmi']))
                                        : null,
                                ];

                                if (filled($data['password'] ?? null)) {
                                    $payload['password'] = Hash::make((string) $data['password']);
                                }

                                $record->update($payload);

                                Notification::make()
                                    ->title('Data Akun Pegawai Diperbarui')
                                    ->body("Data akun {$record->name} berhasil diperbarui.")
                                    ->success()
                                    ->send();
                            })
                    ),

                \Filament\Tables\Columns\TextColumn::make('email_resmi')
                    ->label('Email Resmi')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),


                /*  \Filament\Tables\Columns\TextColumn::make('nomor_absensi')
                    ->label('ID Mesin')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), */

                \Filament\Tables\Columns\TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->badge()
                    ->color(fn(?string $state): string => \App\Models\Bidang::badgeColor($state))
                    ->searchable()
                    ->sortable()
                    ->stickyable(),
                \Filament\Tables\Columns\TextColumn::make('status_kepegawaian')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('nip')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->copyable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('no_kk')
                    ->label('No. KK')
                    ->copyable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP / WA')
                    ->searchable()
                    ->copyable()
                    ->url(fn($state) => filled($state) ? "https://wa.me/" . preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $state)) : null, shouldOpenInNewTab: true)
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('no_bpjs')
                    ->label('BPJS Kesehatan')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                \Filament\Tables\Columns\TextColumn::make('jabatan.nama')
                    ->label('Jabatan')
                    ->badge()
                    ->color(fn($state) => match (true) {
                        str_contains($state ?? '', 'Kepala Dinas') => 'danger',
                        str_contains($state ?? '', 'Sekretaris') => 'warning',
                        str_contains($state ?? '', 'Kabid') => 'purple',
                        str_contains($state ?? '', 'Kasubbag') => 'amber',
                        str_contains($state ?? '', 'Adyatama') => 'rose',
                        str_contains($state ?? '', 'Pelatih') || str_contains($state ?? '', 'Olahraga') => 'teal',
                        str_contains($state ?? '', 'Komputer') || str_contains($state ?? '', 'Sistem') => 'cyan',
                        str_contains($state ?? '', 'Perencana') => 'indigo',
                        str_contains($state ?? '', 'Konservator') => 'emerald',
                        str_contains($state ?? '', 'Hubungan') || str_contains($state ?? '', 'Penerjemah') => 'violet',
                        str_contains($state ?? '', 'Analis Kebijakan') => 'info',
                        str_contains($state ?? '', 'Penelaah Teknis') => 'blue',
                        str_contains($state ?? '', 'Pengelola') || str_contains($state ?? '', 'Penata') => 'lime',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('anggaran_role')
                    ->label('Peran Anggaran')
                    ->badge()
                    ->placeholder('-')
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('jabatan_tim_kreatif')
                    ->label('Tim Kreatif')
                    ->badge()
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('jenjang_pendidikan')
                    ->label('Pendidikan')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'S3' => 'purple',
                        'S2' => 'danger',
                        'S1' => 'success',
                        'D3' => 'info',
                        'SLTA' => 'warning',
                        'SMP' => 'gray',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('pendidikan')
                    ->label('Jurusan / Prodi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('gelar_lengkap')
                    ->label('Nama & Gelar')
                    ->state(fn($record) => trim(($record->gelar_depan ? $record->gelar_depan . ' ' : '') . $record->name . ($record->gelar_belakang ? ', ' . $record->gelar_belakang : '')))
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('agama')
                    ->label('Agama')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('no_telp')
                    ->label('Nomor Telepon')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('npwp')
                    ->label('NPWP')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('tanggal_npwp')
                    ->label('Tanggal NPWP')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('no_taspen')
                    ->label('Nomor Taspen')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('status_kerja')
                    ->label('Tipe Kerja')
                    ->badge()
                    ->color(fn($state) => match (strtolower($state ?? 'wfo')) {
                        'wfh' => 'warning',
                        default => 'success',
                    })
                    ->icon(fn($state) => match (strtolower($state ?? 'wfo')) {
                        'wfh' => 'heroicon-m-home',
                        default => 'heroicon-m-building-office-2',
                    })
                    ->formatStateUsing(fn($state) => strtoupper($state ?? 'WFO')),
                \Filament\Tables\Columns\TextColumn::make('roles.name')
                    ->label('Hak Akses (Role)')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'admin_kepegawaian' => 'warning',
                        'admin_keuangan' => 'emerald',
                        'pa' => 'purple',
                        'ppk' => 'indigo',
                        'pptk' => 'info',
                        'jf' => 'success',
                        'bendahara' => 'amber',
                        'pegawai_kontrak' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin Dinas',
                        'admin_kepegawaian' => 'Admin Kepegawaian',
                        'admin_keuangan' => 'Admin Keuangan',
                        'pa' => 'PA (Kadis)',
                        'ppk' => 'PPK',
                        'pptk' => 'PPTK',
                        'jf' => 'JF',
                        'bendahara' => 'Bendahara',
                        'pegawai' => 'Pegawai',
                        'pegawai_kontrak' => 'Pegawai Kontrak',
                        default => ucwords(str_replace('_', ' ', $state)),
                    })
                    ->searchable()
                    ->sortable(),
                self::roleToggleColumn('akses_kepegawaian', 'Kelola Absensi & Kepegawaian', 'admin_kepegawaian'),
                self::roleToggleColumn('akses_keuangan', 'Kelola Keuangan', 'admin_keuangan'),
                \Filament\Tables\Columns\TextColumn::make('kuota_cuti')
                    ->label('Cuti Tahunan')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-pencil-square')
                    ->formatStateUsing(fn($state): string => (int) ($state ?? 12) . ' Hari')
                    ->tooltip('Klik untuk input / ubah jumlah cuti tahunan (rekap manual)')
                    ->sortable()
                    ->action(
                        Action::make('ubah_kuota_cuti')
                            ->label('Ubah Kuota Cuti Tahunan')
                            ->modalHeading(fn(\App\Models\User $record): string => "Kuota Cuti Tahunan: {$record->name}")
                            ->modalDescription('Input manual jumlah kuota cuti tahunan berdasarkan rekapitulasi kepegawaian.')
                            ->modalWidth('md')
                            ->modalSubmitActionLabel('Simpan Kuota Cuti')
                            ->visible(fn(): bool => Auth::user()?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian']) ?? false)
                            ->fillForm(fn(\App\Models\User $record): array => ['kuota_cuti' => (int) ($record->kuota_cuti ?? 12)])
                            ->form([
                                \Filament\Forms\Components\TextInput::make('kuota_cuti')
                                    ->label('Jumlah Cuti Tahunan (Hari)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(365)
                                    ->suffix('Hari')
                                    ->required(),
                            ])
                            ->action(function (\App\Models\User $record, array $data): void {
                                $kuota = (int) $data['kuota_cuti'];
                                $record->update(['kuota_cuti' => $kuota]);

                                Notification::make()
                                    ->title('Kuota Cuti Tahunan Diperbarui')
                                    ->body("Kuota cuti {$record->name} ditetapkan menjadi {$kuota} hari.")
                                    ->success()
                                    ->send();
                            })
                    ),
                \Filament\Tables\Columns\TextColumn::make('kelengkapan_data')
                    ->label('Kelengkapan')
                    ->badge()
                    ->state(function (\App\Models\User $record): string {
                        $missing = $record->missingKepegawaianFields();
                        return $missing === [] ? 'Lengkap' : 'Kurang ' . count($missing) . ' data';
                    })
                    ->color(fn (string $state): string => $state === 'Lengkap' ? 'success' : 'danger')
                    ->description(function (\App\Models\User $record): ?string {
                        $missing = $record->missingKepegawaianFields();
                        return $missing === [] ? null : implode(', ', $missing);
                    })
                    ->wrap(),
                \Filament\Tables\Columns\ToggleColumn::make('aktif')
                    ->disabled(fn(): bool => ! (Auth::user()?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian']) ?? false)),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('kelengkapan_data')
                    ->label('Kelengkapan Data Kepegawaian')
                    ->placeholder('Semua Pegawai')
                    ->trueLabel('Sudah Lengkap')
                    ->falseLabel('Belum Lengkap')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('nip')->where('nip', '!=', '')
                            ->whereNotNull('nik')->where('nik', '!=', '')
                            ->whereNotNull('no_hp')->where('no_hp', '!=', '')
                            ->whereNotNull('bidang_id')
                            ->whereNotNull('jabatan_id')
                            ->whereNotNull('status_kepegawaian'),
                        false: fn ($query) => $query->where(function ($q) {
                            $q->whereNull('nip')->orWhere('nip', '')
                                ->orWhereNull('nik')->orWhere('nik', '')
                                ->orWhereNull('no_hp')->orWhere('no_hp', '')
                                ->orWhereNull('bidang_id')
                                ->orWhereNull('jabatan_id')
                                ->orWhereNull('status_kepegawaian');
                        }),
                    ),
                SelectFilter::make('status_kepegawaian')
                    ->label('Status Kepegawaian')
                    ->options(\App\Enums\StatusKepegawaian::class),
                SelectFilter::make('jabatan_tim_kreatif')
                    ->label('Tim Kreatif Dinas')
                    ->options(\App\Enums\JabatanTimKreatif::class)
                    ->searchable(),
                SelectFilter::make('jenjang_pendidikan')
                    ->label('Jenjang Pendidikan')
                    ->options([
                        'S3' => 'S3 (Doktoral)',
                        'S2' => 'S2 (Magister)',
                        'S1' => 'S1 / D-IV (Sarjana)',
                        'D3' => 'D3 (Diploma)',
                        'SLTA' => 'SLTA / SMA / SMK',
                        'SMP' => 'SMP / MTs',
                        'SD' => 'SD',
                    ]),
                SelectFilter::make('bidang_id')
                    ->label('Bidang')
                    ->relationship('bidang', 'nama')
                    ->searchable(),
                SelectFilter::make('jabatan_id')
                    ->label('Jabatan')
                    ->relationship('jabatan', 'nama')
                    ->searchable(),
                SelectFilter::make('status_kerja')
                    ->label('Tipe Kerja')
                    ->options([
                        'wfo' => 'WFO (Work From Office)',
                        'wfh' => 'WFH (Work From Home)',
                    ]),
                SelectFilter::make('jadwal_kerja_id')
                    ->label('Shift Kerja')
                    ->relationship('jadwalKerja', 'nama'),
            ])
            ->defaultSort('created_at', 'asc')
            ->recordActions([
                Impersonate::make()
                    ->label(false)
                    ->tooltip('Login sebagai pegawai ini (khusus Super Admin)')
                    ->redirectTo('/admin')
                    ->backTo(fn(): string => \App\Filament\Resources\Users\UserResource::getUrl('index')),
                ViewAction::make()
                    ->label(false),
                EditAction::make()
                    ->label(false)
                    ->visible(fn(): bool => Auth::user()?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian']) ?? false),
                Action::make('reset_device')
                    // ->label('Reset Perangkat')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->color('danger')
                    ->tooltip('Lepas ikatan perangkat Android agar pegawai bisa login di HP baru')
                    ->visible(function (\App\Models\User $record): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return filled($record->device_id)
                            && ($user?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian']) ?? false);
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn(\App\Models\User $record): string => "Reset Perangkat: {$record->name}")
                    ->modalDescription(fn(\App\Models\User $record): string => "Perangkat terikat saat ini: " . ($record->device_name ?? 'Perangkat Android') . ". Reset agar pegawai dapat login pada perangkat baru?")
                    ->action(function (\App\Models\User $record): void {
                        $record->resetDeviceBinding();
                        Notification::make()
                            ->title('Perangkat Berhasil Direset')
                            ->body("Ikatan perangkat {$record->name} telah dilepas.")
                            ->success()
                            ->send();
                    }),
                Action::make('ubah_role')
                    ->label(false)
                    // ->label('Ubah Role')
                    ->icon('heroicon-o-shield-check')
                    ->color('warning')
                    ->tooltip('Ubah Peran & Hak Akses Pegawai')
                    ->visible(function (): bool {
                        /** @var \App\Models\User|null $user */
                        $user = \Illuminate\Support\Facades\Auth::user();

                        return $user?->hasAnyRole(['super_admin', 'admin']) ?? false;
                    })
                    ->modalHeading(fn(\App\Models\User $record): string => "Ubah Role & Hak Akses: {$record->name}")
                    ->modalDescription('Pilih peran resmi untuk pegawai ini. Hak akses dan kewenangan dokumen SPJ akan langsung aktif.')
                    ->modalWidth('lg')
                    ->modalSubmitActionLabel('Simpan Perubahan Role')
                    ->form([
                        CheckboxList::make('roles')
                            ->label('Pilih Peran Resmi (Bisa Lebih Dari Satu)')
                            ->options(function (): array {
                                /** @var \App\Models\User|null $user */
                                $user = \Illuminate\Support\Facades\Auth::user();
                                $isSuperAdmin = $user && $user->hasRole('super_admin');

                                $knownLabels = [
                                    'super_admin' => 'Super Admin Sistem (Akses Penuh IT)',
                                    'admin_kepegawaian' => 'Admin Kepegawaian (Kasubag Umum & Kepegawaian)',
                                    'admin_keuangan' => 'Admin Keuangan (Kelola Master Anggaran, RKA & SPJ)',
                                    'admin' => 'Admin Dinas (Akses Lengkap Umum)',
                                    'pa' => 'PA (Kepala Dinas / Pengguna Anggaran)',
                                    'ppk' => 'PPK (Kasubag Keuangan / Pejabat Pembuat Komitmen)',
                                    'pptk' => 'PPTK (Sekretaris Dinas / Kepala Bidang)',
                                    'jf' => 'JF (Jabatan Fungsional / Pengusul Belanja Sub Kegiatan)',
                                    'bendahara' => 'Bendahara Pengeluaran (Uji Kas & Potong Pajak)',
                                    'pegawai' => 'Pegawai Standar (Presensi GPS Mobile & LHK Pribadi)',
                                    'pegawai_kontrak' => 'Pegawai Kontrak (Izin/Sakit/Cuti, Presensi, Profil & Tim Kreatif)',
                                ];

                                return \Spatie\Permission\Models\Role::query()
                                    ->when(! $isSuperAdmin, fn($q) => $q->where('name', '!=', 'super_admin'))
                                    ->orderBy('id')
                                    ->pluck('name')
                                    ->mapWithKeys(fn(string $roleName): array => [
                                        $roleName => $knownLabels[$roleName] ?? ucwords(str_replace('_', ' ', $roleName)),
                                    ])
                                    ->all();
                            })
                            ->descriptions(function (): array {
                                $knownDescriptions = [
                                    'super_admin' => 'Akses penuh tak terbatas ke seluruh sistem dan konfigurasi teknis.',
                                    'admin_kepegawaian' => 'Mengelola master pegawai, jadwal shift kerja, mesin presensi, dan rekap dinas.',
                                    'admin_keuangan' => 'Mengelola master program, kegiatan, sub kegiatan RKA, barang, toko, dan SPJ belanja.',
                                    'admin' => 'Akses administrator dinas menyeluruh.',
                                    'pa' => 'Memantau seluruh absensi & SPJ, menyetujui tahap akhir SPJ (Kwitansi & NPD).',
                                    'ppk' => 'Memverifikasi administrasi belanja dinas, pagu DPA, dan checklist verifikasi SPJ.',
                                    'pptk' => 'Menandatangani Surat Pesanan dan Kwitansi Pembayaran khusus belanja bidangnya.',
                                    'jf' => 'Membuat draf usulan belanja pada sub kegiatan yang diampunya di bidangnya.',
                                    'bendahara' => 'Menguji kas belanja dinas, memotong pajak, dan menandatangani pelunasan Kwitansi.',
                                    'pegawai' => 'Pegawai ASN/PNS/PPPK untuk presensi harian, izin/cuti, LHK, dan fitur kepegawaian.',
                                    'pegawai_kontrak' => 'Khusus Pegawai Kontrak: hanya Izin/Sakit/Cuti, Profil Pegawai, Presensi, serta Surat Tugas & Tim Kreatif.',
                                ];

                                return \Spatie\Permission\Models\Role::query()
                                    ->pluck('name')
                                    ->mapWithKeys(fn(string $roleName): array => [
                                        $roleName => $knownDescriptions[$roleName] ?? "Hak akses dinamis dari Shield Role ({$roleName}).",
                                    ])
                                    ->all();
                            })
                            ->default(fn(\App\Models\User $record): array => $record->roles->pluck('name')->toArray())
                            ->columns(1)
                            ->required(),
                    ])
                    ->action(function (\App\Models\User $record, array $data) {
                        $selectedRoles = $data['roles'] ?? [];

                        // Pegawai dinas otomatis tetap memiliki role dasar 'pegawai' kecuali memilih role khusus 'pegawai_kontrak'
                        if (!in_array('pegawai', $selectedRoles) && !in_array('pegawai_kontrak', $selectedRoles)) {
                            $selectedRoles[] = 'pegawai';
                        }

                        $record->syncRoles($selectedRoles);

                        // Sinkronkan kolom anggaran_role jika memegang jabatan keuangan resmi
                        $anggaranRole = null;
                        if (in_array('pa', $selectedRoles)) {
                            $anggaranRole = \App\Enums\AnggaranRole::PA;
                        } elseif (in_array('ppk', $selectedRoles)) {
                            $anggaranRole = \App\Enums\AnggaranRole::PPK;
                        } elseif (in_array('pptk', $selectedRoles)) {
                            $anggaranRole = \App\Enums\AnggaranRole::PPTK;
                        } elseif (in_array('bendahara', $selectedRoles)) {
                            $anggaranRole = \App\Enums\AnggaranRole::BENDAHARA;
                        }
                        $record->update(['anggaran_role' => $anggaranRole]);

                        Notification::make()
                            ->title('Peran Pegawai Berhasil Diperbarui')
                            ->body("Role untuk {$record->name} kini adalah: " . implode(', ', $selectedRoles))
                            ->success()
                            ->send();
                    }),
                /* Action::make('hubungkan_mesin')
                    ->label('Hubungkan ke Mesin')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->disabled()
                    ->tooltip('Tombol hubungkan ke mesin dinonaktifkan.')
                    ->visible(fn(\App\Models\User $record) => empty($record->nomor_absensi))
                    ->form([
                        Select::make('nomor_absensi')
                            ->label('Pilih Nama dari Mesin')
                            ->options(function () {
                                $mesin = \App\Models\MesinAbsensi::where('aktif', true)->first();
                                if (!$mesin) return [];
                                try {
                                    $service = new \App\Services\ZKTecoService($mesin->alamat_ip, $mesin->port);
                                    $users = $service->getUsers();
                                    $existingPins = \App\Models\User::query()->where('nomor_absensi', '!=', null, 'and')->pluck('nomor_absensi')->toArray();

                                    $options = [];
                                    foreach ($users as $u) {
                                        if (!in_array($u['userid'], $existingPins)) {
                                            $options[$u['userid']] = $u['userid'] . ' - ' . $u['name'];
                                        }
                                    }
                                    return $options;
                                } catch (\Exception $e) {
                                    return [];
                                }
                            })
                            ->searchable()
                            ->required()
                    ])
                    ->action(function (\App\Models\User $record, array $data) {
                        $record->update(['nomor_absensi' => $data['nomor_absensi']]);
                        Notification::make()->title('Pegawai berhasil dihubungkan dengan mesin!')->success()->send();
                    }), */
                /* Action::make('kirim_ke_mesin')
                    ->label('Kirim ke Mesin')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('gray')
                    ->disabled()
                    ->tooltip('Tombol kirim ke mesin dinonaktifkan.')
                    ->visible(fn(): bool => Auth::user()?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian']) ?? false)
                    ->form([
                        \Filament\Forms\Components\TextInput::make('nomor_absensi')
                            ->label('Nomor ID di Mesin (PIN)')
                            ->numeric()
                            ->required()
                            ->default(function (\App\Models\User $record) {
                                if (!empty($record->nomor_absensi)) {
                                    return $record->nomor_absensi;
                                }
                                $maxId = (int) \App\Models\User::max('nomor_absensi');
                                return $maxId > 0 ? (string)($maxId + 1) : '1';
                            })
                    ])
                    ->action(function (\App\Models\User $record, array $data) {
                        $mesin = \App\Models\MesinAbsensi::where('aktif', true)->first();
                        if (!$mesin) {
                            Notification::make()->title('Mesin Absensi Aktif tidak ditemukan!')->danger()->send();
                            return;
                        }

                        try {
                            $service = new \App\Services\ZKTecoService($mesin->alamat_ip, $mesin->port);

                            $uid = $record->id < 65000 ? $record->id : rand(100, 65000);
                            $pin = $data['nomor_absensi'];
                            $name = substr($record->name, 0, 24); // Maksimal 24 karakter

                            $service->setUser($uid, $pin, $name, '', 0, 0);

                            if ($record->nomor_absensi != $pin) {
                                $record->update(['nomor_absensi' => $pin]);
                            }

                            Notification::make()->title('Data pegawai berhasil dikirim & didaftarkan ke mesin!')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Gagal mengirim ke mesin: ' . $e->getMessage())->danger()->send();
                        }
                    }) */
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    BulkAction::make('ubah_role_massal')
                        ->label('Ubah Role Massal')
                        ->icon('heroicon-o-shield-check')
                        ->color('warning')
                        ->visible(function (): bool {
                            /** @var \App\Models\User|null $user */
                            $user = \Illuminate\Support\Facades\Auth::user();

                            return $user?->hasAnyRole(['super_admin', 'admin']) ?? false;
                        })
                        ->form([
                            Select::make('mode')
                                ->label('Pilihan Aksi Role')
                                ->options([
                                    'tambah' => 'Tambahkan Role Tertentu (Role yang Ada Tetap Dipertahankan)',
                                    'ganti' => 'Ganti Peran Menjadi Role Baru (Reset Role Lama)',
                                ])
                                ->default('tambah')
                                ->required(),
                            Select::make('role')
                                ->label('Pilih Role Target')
                                ->options([
                                    'jf' => 'JF (Jabatan Fungsional / Pengusul Belanja)',
                                    'pptk' => 'PPTK (Sekdis / Kepala Bidang)',
                                    'admin_kepegawaian' => 'Admin Kepegawaian (Umum & Kepegawaian)',
                                    'admin_keuangan' => 'Admin Keuangan (Master Anggaran & SPJ)',
                                    'admin' => 'Admin Dinas (Akses Umum)',
                                    'pegawai' => 'Pegawai Biasa (Staf Pelaksana)',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $count = $records->count();
                            $targetRole = $data['role'];
                            $mode = $data['mode'];

                            foreach ($records as $record) {
                                if ($mode === 'tambah') {
                                    $record->assignRole($targetRole);
                                } else {
                                    $roles = [$targetRole];
                                    if ($targetRole !== 'pegawai') {
                                        $roles[] = 'pegawai';
                                    }
                                    $record->syncRoles($roles);
                                }

                                // Update anggaran_role jika PPTK
                                if ($targetRole === 'pptk') {
                                    $record->update(['anggaran_role' => \App\Enums\AnggaranRole::PPTK]);
                                } elseif ($mode === 'ganti' && $targetRole === 'pegawai') {
                                    $record->update(['anggaran_role' => null]);
                                }
                            }

                            Notification::make()
                                ->title("Berhasil Memperbarui Role untuk {$count} Pegawai")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('ubah_tipe_kerja_massal')
                        ->label('Ubah Tipe Kerja (WFH/WFO)')
                        ->icon('heroicon-o-home-modern')
                        ->color('warning')
                        ->form([
                            Select::make('status_kerja')
                                ->label('Pilih Tipe Kerja')
                                ->options([
                                    'wfo' => 'WFO (Work From Office)',
                                    'wfh' => 'WFH (Work From Home)',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $count = $records->count();
                            $records->each(fn($user) => $user->update(['status_kerja' => $data['status_kerja']]));

                            Notification::make()
                                ->title("Berhasil mengubah tipe kerja $count pegawai menjadi " . strtoupper($data['status_kerja']))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('ubah_shift_massal')
                        ->label('Ubah Shift / Jadwal Kerja (Massal)')
                        ->icon('heroicon-o-clock')
                        ->color('success')
                        ->form([
                            Select::make('jadwal_kerja_id')
                                ->label('Pilih Shift / Jadwal Kerja')
                                ->relationship('jadwalKerja', 'nama')
                                ->placeholder('Gunakan Jadwal Default Instansi')
                                ->searchable()
                                ->preload(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $count = $records->count();
                            $records->each(fn($user) => $user->update(['jadwal_kerja_id' => $data['jadwal_kerja_id'] ?? null]));

                            Notification::make()
                                ->title("Berhasil memperbarui shift kerja $count pegawai.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('tempatkan_ke_jf_massal')
                        ->label('Tempatkan ke JF (Sesuai SK)')
                        ->icon('heroicon-o-user-group')
                        ->color('success')
                        ->modalHeading('Tempatkan Staf ke JF')
                        ->modalDescription('Staf terpilih ditempatkan di bawah JF sesuai SK penempatan. Atasan langsung tetap Kepala Bidang.')
                        ->form([
                            Select::make('jf_id')
                                ->label('Pilih JF Tujuan')
                                ->options(fn(): array => \App\Models\User::query()
                                    ->role('jf')
                                    ->where('aktif', '=', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $jf = \App\Models\User::query()->findOrFail($data['jf_id']);
                            $kepalaId = $jf->bidang_id
                                ? \App\Models\Bidang::query()->whereKey($jf->bidang_id)->value('kepala_id')
                                : null;

                            $count = 0;
                            foreach ($records as $staf) {
                                if ($staf->getKey() === $jf->getKey()) {
                                    continue;
                                }

                                $staf->update([
                                    'jf_id' => $jf->getKey(),
                                    'bidang_id' => $jf->bidang_id,
                                    'atasan_id' => $kepalaId && $kepalaId !== $staf->getKey() ? $kepalaId : $staf->atasan_id,
                                ]);
                                $count++;
                            }

                            if ($kepalaId && $jf->atasan_id !== $kepalaId && $jf->getKey() !== $kepalaId) {
                                $jf->update(['atasan_id' => $kepalaId]);
                            }

                            Notification::make()
                                ->title("{$count} staf ditempatkan di bawah {$jf->name}")
                                ->body('Atasan langsung tetap Kepala Bidang.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('ubah_bidang_massal')
                        ->label('Ubah Bidang (Massal)')
                        ->icon('heroicon-o-building-office')
                        ->color('info')
                        ->form([
                            Select::make('bidang_id')
                                ->label('Pilih Bidang Tujuan')
                                ->options(function (mixed $livewire): array {
                                    $selectedRecords = (is_object($livewire) && method_exists($livewire, 'getSelectedTableRecords'))
                                        ? $livewire->getSelectedTableRecords()
                                        : collect();
                                    $totalSelected = $selectedRecords->count();

                                    return \App\Models\Bidang::query()
                                        ->orderBy('nama')
                                        ->get()
                                        ->mapWithKeys(function (\App\Models\Bidang $bidang) use ($selectedRecords, $totalSelected) {
                                            $alreadyCount = $selectedRecords->where('bidang_id', $bidang->id)->count();
                                            if ($totalSelected > 0 && $alreadyCount === $totalSelected) {
                                                return [
                                                    $bidang->id => "{$bidang->nama} (⚠️ Seluruh {$alreadyCount} pegawai terpilih sudah di bidang ini)",
                                                ];
                                            }
                                            if ($alreadyCount > 0) {
                                                return [
                                                    $bidang->id => "{$bidang->nama} (⚠️ {$alreadyCount} dari {$totalSelected} pegawai sudah di bidang ini)",
                                                ];
                                            }

                                            return [$bidang->id => $bidang->nama];
                                        })
                                        ->all();
                                })
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, mixed $livewire) {
                                    if (! $state || ! is_object($livewire) || ! method_exists($livewire, 'getSelectedTableRecords')) {
                                        return;
                                    }

                                    $selectedRecords = $livewire->getSelectedTableRecords();
                                    $alreadyIn = $selectedRecords->where('bidang_id', (int) $state);

                                    if ($alreadyIn->isNotEmpty()) {
                                        $bidangNama = \App\Models\Bidang::whereKey((int) $state)->value('nama') ?? 'Bidang tersebut';
                                        $names = $alreadyIn->pluck('name')->implode(', ');

                                        Notification::make()
                                            ->warning()
                                            ->title('Peringatan: Pegawai Sudah di Bidang Ini')
                                            ->body("Pegawai ({$names}) sudah terdaftar di {$bidangNama}.")
                                            ->send();
                                    }
                                })
                                ->required(),

                            \Filament\Forms\Components\Placeholder::make('peringatan_bidang_sama')
                                ->hiddenLabel()
                                ->visible(function (callable $get, mixed $livewire): bool {
                                    $bidangId = $get('bidang_id');
                                    if (! $bidangId || ! is_object($livewire) || ! method_exists($livewire, 'getSelectedTableRecords')) {
                                        return false;
                                    }

                                    return $livewire->getSelectedTableRecords()->where('bidang_id', (int) $bidangId)->isNotEmpty();
                                })
                                ->content(function (callable $get, mixed $livewire): \Illuminate\Support\HtmlString {
                                    $bidangId = (int) $get('bidang_id');
                                    $selectedRecords = (is_object($livewire) && method_exists($livewire, 'getSelectedTableRecords'))
                                        ? $livewire->getSelectedTableRecords()
                                        : collect();
                                    $alreadyIn = $selectedRecords->where('bidang_id', $bidangId);
                                    $toMove = $selectedRecords->where('bidang_id', '!=', $bidangId);
                                    $bidangNama = e(\App\Models\Bidang::whereKey($bidangId)->value('nama') ?? 'Bidang terpilih');
                                    $alreadyNames = e($alreadyIn->pluck('name')->implode(', '));

                                    if ($toMove->isEmpty()) {
                                        return new \Illuminate\Support\HtmlString(
                                            "<div style='padding:10px 12px; border-radius:8px; background-color:#fef2f2; border:1px solid #f87171; color:#991b1b; font-size:0.85rem; line-height:1.45;'>
                                                <strong>⚠️ Semua Pegawai Terpilih Sudah Berada di {$bidangNama}!</strong><br>
                                                Pegawai: <strong>{$alreadyNames}</strong>.<br>
                                                Silakan pilih bidang tujuan yang berbeda agar pemindahan efektif.
                                            </div>"
                                        );
                                    }

                                    $moveNames = e($toMove->pluck('name')->implode(', '));

                                    return new \Illuminate\Support\HtmlString(
                                        "<div style='padding:10px 12px; border-radius:8px; background-color:#fffbeb; border:1px solid #fbbf24; color:#92400e; font-size:0.85rem; line-height:1.45;'>
                                            <strong>⚠️ Peringatan Duplikasi Bidang ({$bidangNama}):</strong><br>
                                            • Sudah berada di bidang ini (akan dilewati): <strong>{$alreadyNames}</strong><br>
                                            • Akan dipindahkan ({$toMove->count()} pegawai): <strong>{$moveNames}</strong>
                                        </div>"
                                    );
                                }),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $targetBidangId = (int) $data['bidang_id'];
                            $targetBidang = \App\Models\Bidang::find($targetBidangId);
                            $namaBidang = $targetBidang?->nama ?? 'bidang tujuan';

                            $alreadyIn = $records->filter(fn($user) => (int) $user->bidang_id === $targetBidangId);
                            $toMove = $records->reject(fn($user) => (int) $user->bidang_id === $targetBidangId);

                            if ($toMove->isEmpty()) {
                                $alreadyNames = $alreadyIn->pluck('name')->implode(', ');
                                Notification::make()
                                    ->warning()
                                    ->title('Tidak Ada Pegawai yang Dipindahkan')
                                    ->body("Seluruh pegawai terpilih ({$alreadyNames}) sudah berada di {$namaBidang}.")
                                    ->send();

                                return;
                            }

                            $toMove->each(fn($user) => $user->update([
                                'bidang_id' => $targetBidangId,
                                'atasan_id' => ($targetBidang?->kepala_id && $user->id !== $targetBidang->kepala_id)
                                    ? $targetBidang->kepala_id
                                    : $user->atasan_id,
                            ]));

                            if ($alreadyIn->isNotEmpty()) {
                                $skippedNames = $alreadyIn->pluck('name')->implode(', ');
                                Notification::make()
                                    ->warning()
                                    ->title("Berhasil memindahkan {$toMove->count()} pegawai ke {$namaBidang}")
                                    ->body("{$alreadyIn->count()} pegawai ({$skippedNames}) dilewati karena sudah berada di {$namaBidang}.")
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title("Berhasil memindahkan {$toMove->count()} pegawai ke {$namaBidang}.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('ubah_role_massal')
                        ->label('Ubah Hak Akses / Role (Massal)')
                        ->icon('heroicon-o-shield-check')
                        ->color('primary')
                        ->form([
                            Select::make('roles')
                                ->label('Pilih Role Baru')
                                ->relationship('roles', 'name', function ($query) {
                                    /** @var \App\Models\User|null $user */
                                    $user = \Illuminate\Support\Facades\Auth::user();

                                    return $user?->hasRole('super_admin')
                                        ? $query
                                        : $query->where('name', '!=', 'super_admin');
                                })
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $count = $records->count();
                            foreach ($records as $user) {
                                $user->syncRoles($data['roles']);
                            }

                            Notification::make()
                                ->title("Berhasil memperbarui hak akses untuk $count pegawai.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('ubah_kuota_cuti_massal')
                        ->label('Input Kuota Cuti Tahunan (Massal)')
                        ->icon('heroicon-o-calendar-days')
                        ->color('info')
                        ->visible(fn(): bool => Auth::user()?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian']) ?? false)
                        ->form([
                            \Filament\Forms\Components\TextInput::make('kuota_cuti')
                                ->label('Jumlah Cuti Tahunan (Hari)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(365)
                                ->default(12)
                                ->suffix('Hari')
                                ->required()
                                ->helperText('Nilai ini akan diterapkan ke seluruh pegawai yang dipilih.'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $kuota = (int) $data['kuota_cuti'];
                            $count = $records->count();
                            $records->toQuery()->update(['kuota_cuti' => $kuota]);

                            Notification::make()
                                ->title("Kuota Cuti Tahunan {$count} Pegawai Diperbarui")
                                ->body("Kuota cuti tahunan ditetapkan menjadi {$kuota} hari.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('ubah_jadwal_kerja_massal')
                        ->label('Ubah Shift Kerja (Massal)')
                        ->icon('heroicon-o-clock')
                        ->color('secondary')
                        ->form([
                            Select::make('jadwal_kerja_id')
                                ->label('Pilih Jadwal / Shift Kerja')
                                ->relationship('jadwalKerja', 'nama')
                                ->placeholder('Gunakan Jadwal Default Sistem')
                                ->searchable()
                                ->preload(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $count = $records->count();
                            $records->each(fn($user) => $user->update(['jadwal_kerja_id' => $data['jadwal_kerja_id']]));

                            Notification::make()
                                ->title("Berhasil menerapkan shift kerja untuk $count pegawai.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    \Filament\Actions\BulkAction::make('kirim_ke_mesin_bulk')
                        ->label('Kirim ke Mesin (Massal)')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('gray')
                        ->disabled()
                        ->tooltip('Tombol kirim ke mesin dinonaktifkan.')
                        ->requiresConfirmation()
                        ->modalDescription('Pegawai yang belum memiliki PIN/ID Mesin akan otomatis menggunakan ID Web-nya sebagai PIN di mesin. Lanjutkan mengirim data terpilih ke mesin?')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $mesin = \App\Models\MesinAbsensi::where('aktif', true)->first();
                            if (!$mesin) {
                                Notification::make()->title('Mesin Absensi Aktif tidak ditemukan!')->danger()->send();
                                return;
                            }

                            try {
                                $service = new \App\Services\ZKTecoService($mesin->alamat_ip, $mesin->port);
                                $berhasil = 0;

                                foreach ($records as $record) {
                                    $pin = $record->nomor_absensi;
                                    if (empty($pin)) {
                                        $pin = (string)$record->id;
                                        $record->update(['nomor_absensi' => $pin]);
                                    }

                                    $uid = $record->id < 65000 ? $record->id : rand(100, 65000);
                                    $name = substr($record->name, 0, 24);

                                    $service->setUser($uid, $pin, $name, '', 0, 0);
                                    $berhasil++;
                                }

                                Notification::make()->title("Berhasil mengirim $berhasil data pegawai ke mesin!")->success()->send();
                            } catch (\Exception $e) {
                                Notification::make()->title('Gagal mengirim ke mesin: ' . $e->getMessage())->danger()->send();
                            }
                        }),
                ]),
            ])
            ->headerActions([
                Action::make('cetak_bezeting')
                    ->label('Cetak Bezeting Pegawai')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(route('pegawai.cetak-bezeting'), shouldOpenInNewTab: true)
                    ->visible(fn (): bool => Auth::user()?->hasAnyRole(['super_admin', 'superadmin', 'admin', 'admin_kepegawaian', 'sekretaris', 'kepala_dinas']) ?? false),
                Action::make('cetak_belum_lengkap')
                    ->label('Laporan Belum Lengkap')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->url(route('pegawai.cetak-belum-lengkap'), shouldOpenInNewTab: true)
                    ->visible(fn (): bool => Auth::user()?->hasAnyRole(['super_admin', 'superadmin', 'admin', 'admin_kepegawaian', 'sekretaris', 'kepala_dinas']) ?? false),
                Action::make('ingatkan_belum_lengkap')
                    ->label('Ingatkan Pegawai')
                    ->icon('heroicon-o-bell-alert')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Pengingat Data Kepegawaian')
                    ->modalDescription('Kirim notifikasi ke semua pegawai aktif yang data kepegawaiannya belum lengkap?')
                    ->visible(fn (): bool => Auth::user()?->hasAnyRole(['super_admin', 'superadmin', 'admin', 'admin_kepegawaian']) ?? false)
                    ->action(function (): void {
                        $targets = \App\Models\User::query()
                            ->where('aktif', true)
                            ->where(function ($q) {
                                $q->whereNull('nip')->orWhere('nip', '')
                                    ->orWhereNull('nik')->orWhere('nik', '')
                                    ->orWhereNull('no_hp')->orWhere('no_hp', '')
                                    ->orWhereNull('bidang_id')
                                    ->orWhereNull('jabatan_id')
                                    ->orWhereNull('status_kepegawaian');
                            })
                            ->get();

                        if ($targets->isEmpty()) {
                            Notification::make()->title('Semua pegawai sudah melengkapi data.')->success()->send();
                            return;
                        }

                        \App\Services\PushNotificationService::sendToUsers(
                            $targets,
                            'Lengkapi Data Kepegawaian',
                            'Mohon segera melengkapi profil kepegawaian Anda (NIP, NIK, No. HP, Bidang, Jabatan) di menu Profil.',
                            ['url' => route('mobile.profil')],
                            'warning'
                        );

                        Notification::make()
                            ->title("Pengingat dikirim ke {$targets->count()} pegawai.")
                            ->success()
                            ->send();
                    }),
                CreateAction::make()
                    ->label('Tambah Pegawai')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->url(\App\Filament\Resources\Users\UserResource::getUrl('create')),
            ]);
    }

    /**
     * Toggle langsung di tabel pegawai untuk mendelegasikan akses admin bantu
     * (Absensi & Kepegawaian / Keuangan) tanpa menghapus role pegawai.
     */
    protected static function roleToggleColumn(string $name, string $label, string $roleName): \Filament\Tables\Columns\ToggleColumn
    {
        return \Filament\Tables\Columns\ToggleColumn::make($name)
            ->label($label)
            ->onColor('success')
            ->offColor('gray')
            ->visible(fn (): bool => Auth::user()?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian']) ?? false)
            ->getStateUsing(fn (\App\Models\User $record): bool => $record->hasRole($roleName))
            ->updateStateUsing(function (\App\Models\User $record, bool $state) use ($roleName, $label): void {
                if ($state) {
                    $record->assignRole($roleName);
                } else {
                    $record->removeRole($roleName);
                }

                Notification::make()
                    ->title(($state ? 'Akses diaktifkan: ' : 'Akses dicabut: ') . $label)
                    ->body($record->name)
                    ->success()
                    ->send();
            });
    }
}
