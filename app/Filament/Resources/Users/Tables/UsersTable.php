<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('nip')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('jabatan.nama')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('nomor_absensi')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('roles.name')->badge(),
                \Filament\Tables\Columns\ToggleColumn::make('aktif'),
            ])
            ->filters([
                SelectFilter::make('jabatan_id')
                    ->label('Jabatan')
                    ->relationship('jabatan', 'nama')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('sync_mesin')
                    ->label('Sync Pegawai Mesin')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->action(function () {
                        $mesin = \App\Models\MesinAbsensi::where('aktif', true)->first();
                        if (!$mesin) {
                            \Filament\Notifications\Notification::make()->title('Mesin Absensi Aktif tidak ditemukan!')->danger()->send();
                            return;
                        }

                        try {
                            $service = new \App\Services\ZKTecoService($mesin->alamat_ip, $mesin->port);
                            $users = $service->getUsers();

                            $newUsers = 0;
                            foreach ($users as $u) {
                                $pin = $u['userid'];
                                $name = $u['name'];

                                $existing = \App\Models\User::where('nomor_absensi', $pin)->first();
                                if (!$existing) {
                                    $existingByName = \App\Models\User::where('name', $name)->first();
                                    if ($existingByName) {
                                        $existingByName->update(['nomor_absensi' => $pin]);
                                    } else {
                                        $newUser = \App\Models\User::create([
                                            'name' => $name,
                                            'nomor_absensi' => $pin,
                                            'email' => strtolower(str_replace([' ', '.', ','], '', $name)) . rand(100, 999) . '@example.com',
                                            'password' => \Illuminate\Support\Facades\Hash::make('password'),
                                        ]);
                                        $newUser->assignRole('pegawai');
                                        $newUsers++;
                                    }
                                }
                            }
                            \Filament\Notifications\Notification::make()->title("Berhasil sinkronisasi. $newUsers pegawai baru ditambahkan.")->success()->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()->title('Gagal terhubung ke mesin: ' . $e->getMessage())->danger()->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Sync Pegawai dari Mesin Absensi')
                    ->modalDescription('Aksi ini akan mengambil daftar pegawai dari mesin absensi dan mendaftarkannya ke sistem jika belum ada. Lanjutkan?'),
            ]);
    }
}
