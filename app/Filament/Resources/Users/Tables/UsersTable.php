<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (\Illuminate\Database\Eloquent\Builder $query) {
                if ($jenjang = request()->query('jenjang')) {
                    $query->where('jenjang_pendidikan', '=', $jenjang);
                }
            })
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('avatar_url')
                    ->disk('public')
                    ->label('Avatar')
                    ->circular(),
                \Filament\Tables\Columns\TextColumn::make('nomor_absensi')
                    ->label('ID Mesin')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->url(fn ($record) => \App\Filament\Resources\Users\UserResource::getUrl('view', ['record' => $record])),
                \Filament\Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('nip')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->copyable()
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: false),
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
                    ->toggleable(isToggledHiddenByDefault: false),
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
                \Filament\Tables\Columns\TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('anggaran_role')
                    ->label('Peran Anggaran')
                    ->badge()
                    ->placeholder('-')
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
                    ->toggleable(isToggledHiddenByDefault: false),
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
                \Filament\Tables\Columns\TextColumn::make('email_pribadi')
                    ->label('Email Pribadi')
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
                \Filament\Tables\Columns\TextColumn::make('roles.name')->badge(),
                \Filament\Tables\Columns\ToggleColumn::make('aktif'),
            ])
            ->filters([
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
                    ->searchable()
                    ->preload(),
                SelectFilter::make('jabatan_id')
                    ->label('Jabatan')
                    ->relationship('jabatan', 'nama')
                    ->searchable()
                    ->preload(),
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
            ->headerActions([
                Action::make('buat_pegawai')
                    ->label('Tambah Pegawai')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->url(fn(): string => \App\Filament\Resources\Users\UserResource::getUrl('create')),

                Action::make('kelola_bidang')
                    ->label('Kelola Bidang')
                    ->icon('heroicon-o-building-office-2')
                    ->color('gray')
                    ->url(fn(): string => \App\Filament\Resources\Bidangs\BidangResource::getUrl('index')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('hubungkan_mesin')
                    ->label('Hubungkan ke Mesin')
                    ->icon('heroicon-o-link')
                    ->color('warning')
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
                    }),
                Action::make('kirim_ke_mesin')
                    ->label('Kirim ke Mesin')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
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
                    })
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

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

                            $alreadyIn = $records->filter(fn ($user) => (int) $user->bidang_id === $targetBidangId);
                            $toMove = $records->reject(fn ($user) => (int) $user->bidang_id === $targetBidangId);

                            if ($toMove->isEmpty()) {
                                $alreadyNames = $alreadyIn->pluck('name')->implode(', ');
                                Notification::make()
                                    ->warning()
                                    ->title('Tidak Ada Pegawai yang Dipindahkan')
                                    ->body("Seluruh pegawai terpilih ({$alreadyNames}) sudah berada di {$namaBidang}.")
                                    ->send();

                                return;
                            }

                            $toMove->each(fn ($user) => $user->update([
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
                                ->relationship('roles', 'name')
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
                        ->color('success')
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
                /* Action::make('sync_mesin')
                    ->label('Tarik Pegawai Mesin (Buat Baru)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        Select::make('pegawai_terpilih')
                            ->label('Pilih Pegawai yang ingin ditambahkan ke Web')
                            ->multiple()
                            ->searchable()
                            ->required()
                            ->options(function () {
                                $mesin = \App\Models\MesinAbsensi::where('aktif', true)->first();
                                if (!$mesin) return [];
                                try {
                                    $service = new \App\Services\ZKTecoService($mesin->alamat_ip, $mesin->port);
                                    $users = $service->getUsers();
                                    $existingPins = \App\Models\User::whereNotNull('nomor_absensi')->pluck('nomor_absensi')->toArray();
                                    
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
                    ])
                    ->action(function (array $data) {
                        $mesin = \App\Models\MesinAbsensi::where('aktif', true)->first();
                        if (!$mesin) {
                            Notification::make()->title('Mesin Absensi Aktif tidak ditemukan!')->danger()->send();
                            return;
                        }

                        try {
                            $service = new \App\Services\ZKTecoService($mesin->alamat_ip, $mesin->port);
                            $users = $service->getUsers();
                            
                            $terpilih = $data['pegawai_terpilih'] ?? [];
                            $newUsers = 0;

                            foreach ($users as $u) {
                                if (in_array($u['userid'], $terpilih)) {
                                    $newUser = \App\Models\User::create([
                                        'name' => $u['name'],
                                        'nomor_absensi' => $u['userid'],
                                        'email' => strtolower(str_replace([' ', '.', ','], '', $u['name'])) . rand(100, 999) . '@example.com',
                                        'password' => \Illuminate\Support\Facades\Hash::make('password'),
                                    ]);
                                    $newUser->assignRole('pegawai');
                                    $newUsers++;
                                }
                            }
                            Notification::make()->title("Berhasil menambahkan $newUsers pegawai baru.")->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Gagal terhubung ke mesin: ' . $e->getMessage())->danger()->send();
                        }
                    })
                    ->modalHeading('Tarik Pegawai dari Mesin Absensi')
                    ->modalDescription('Pilih nama-nama pegawai dari mesin yang belum ada di database untuk dibuatkan akun barunya.'), */]);
    }
}
