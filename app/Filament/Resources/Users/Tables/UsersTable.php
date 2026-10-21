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
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('nip')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: false),
                \Filament\Tables\Columns\TextColumn::make('no_kk')
                    ->label('No. KK')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP / WA')
                    ->searchable()
                    ->copyable()
                    ->url(fn ($state) => filled($state) ? "https://wa.me/" . preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $state)) : null, shouldOpenInNewTab: true)
                    ->toggleable(isToggledHiddenByDefault: false),
                \Filament\Tables\Columns\TextColumn::make('no_bpjs')
                    ->label('BPJS Kesehatan')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('jabatan.nama')
                    ->label('Jabatan')
                    ->badge()
                    ->color(fn ($state) => match (true) {
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

                \Filament\Tables\Columns\TextColumn::make('status_kerja')
                    ->label('Tipe Kerja')
                    ->badge()
                    ->color(fn ($state) => match(strtolower($state ?? 'wfo')) {
                        'wfo' => 'success',
                        'wfh' => 'warning',
                        'fleksibel' => 'info',
                        default => 'gray',
                    })
                    ->icon(fn ($state) => match(strtolower($state ?? 'wfo')) {
                        'wfo' => 'heroicon-m-building-office-2',
                        'wfh' => 'heroicon-m-home',
                        'fleksibel' => 'heroicon-m-arrows-pointing-out',
                        default => 'heroicon-m-building-office-2',
                    })
                    ->formatStateUsing(fn ($state) => strtoupper($state ?? 'WFO')),
                \Filament\Tables\Columns\TextColumn::make('roles.name')->badge(),
                \Filament\Tables\Columns\ToggleColumn::make('aktif'),
            ])
            ->filters([
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
                        'fleksibel' => 'Fleksibel (Hybrid)',
                    ]),
                SelectFilter::make('jadwal_kerja_id')
                    ->label('Shift Kerja')
                    ->relationship('jadwalKerja', 'nama'),
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
                                    'fleksibel' => 'Fleksibel (Hybrid)',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $count = $records->count();
                            $records->each(fn ($user) => $user->update(['status_kerja' => $data['status_kerja']]));

                            Notification::make()
                                ->title("Berhasil mengubah tipe kerja $count pegawai menjadi " . strtoupper($data['status_kerja']))
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
                                ->relationship('bidang', 'nama')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $count = $records->count();
                            $records->each(fn ($user) => $user->update(['bidang_id' => $data['bidang_id']]));

                            Notification::make()
                                ->title("Berhasil memindahkan $count pegawai ke bidang yang dipilih.")
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
                            $records->each(fn ($user) => $user->update(['jadwal_kerja_id' => $data['jadwal_kerja_id']]));

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
