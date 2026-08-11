<?php

namespace App\Filament\Resources\LogAbsensis\Tables;

use App\Models\LogAbsensi;
use App\Models\MesinAbsensi;
use App\Models\User;
use App\Services\AbsensiHarianService;
use App\Services\ZKTecoService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LogAbsensisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['user', 'mesinAbsensi']))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('mesinAbsensi.nama')
                    ->label('Mesin')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nomor_absensi')
                    ->searchable(),
                TextColumn::make('waktu_absensi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('sumber')
                    ->searchable(),
                TextColumn::make('metode_verifikasi')
                    ->searchable(),
                TextColumn::make('kode_kerja')
                    ->searchable(),
                TextColumn::make('kode_log_asli')
                    ->searchable(),
            ])
            ->filters([
                //
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
                Action::make('tarik_log')
                    ->label('Tarik Log Absensi Mesin')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        $mesin = MesinAbsensi::where('aktif', true)->first();
                        if (!$mesin) {
                            Notification::make()
                                ->title('Mesin Absensi Aktif tidak ditemukan!')
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            $service = new ZKTecoService($mesin->alamat_ip, $mesin->port);
                            $attendance = $service->getAttendance();

                            $newLogs = 0;
                            $tanggalDiproses = [];

                            foreach ($attendance as $log) {
                                $pin   = $log['id'];
                                $waktu = date('Y-m-d H:i:s', strtotime($log['timestamp']));

                                // Cari user berdasarkan nomor_absensi (PIN)
                                $user = User::where('nomor_absensi', $pin)->first();

                                // Simpan hanya jika log belum ada
                                $exists = LogAbsensi::where('nomor_absensi', $pin)
                                    ->where('waktu_absensi', $waktu)
                                    ->exists();

                                if (!$exists) {
                                    LogAbsensi::create([
                                        'user_id'           => $user?->id,
                                        'mesin_absensi_id'  => $mesin->id,
                                        'nomor_absensi'     => $pin,
                                        'waktu_absensi'     => $waktu,
                                        'sumber'            => 'mesin',
                                        'metode_verifikasi' => $log['type'] ?? null,
                                        'kode_kerja'        => $log['state'] ?? null,
                                        'kode_log_asli'     => $log['uid'] ?? null,
                                    ]);
                                    $newLogs++;

                                    // Kumpulkan user_id + tanggal yang perlu diproses
                                    if ($user) {
                                        $tanggal = date('Y-m-d', strtotime($waktu));
                                        $tanggalDiproses[$user->id . '|' . $tanggal] = [
                                            'user_id' => $user->id,
                                            'tanggal' => $tanggal,
                                        ];
                                    }
                                }
                            }

                            // Perbarui sinkronisasi_terakhir mesin
                            $mesin->update(['sinkronisasi_terakhir' => now()]);

                            // Proses log menjadi absensi_harian
                            $absensiService = new AbsensiHarianService();
                            $prosesCount = 0;
                            foreach ($tanggalDiproses as $item) {
                                $prosesCount += $absensiService->prosesLog($item['tanggal'], $item['user_id']);
                            }

                            $message = "Berhasil menarik $newLogs log absensi baru.";
                            if ($prosesCount > 0) {
                                $message .= " $prosesCount data absensi harian diperbarui.";
                            }

                            Notification::make()
                                ->title($message)
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal terhubung ke mesin: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Tarik Log Absensi')
                    ->modalDescription('Aksi ini akan mengunduh log kehadiran dari mesin, menyimpan log baru, dan memperbarui data absensi harian secara otomatis. Lanjutkan?'),

                Action::make('test_koneksi')
                    ->label('Test Koneksi')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function () {
                        $mesin = MesinAbsensi::where('aktif', true)->first();
                        if (!$mesin) {
                            Notification::make()
                                ->title('Mesin Absensi Aktif tidak ditemukan!')
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            $service = new ZKTecoService($mesin->alamat_ip, $mesin->port);
                            $connected = $service->connect();
                            $service->disconnect();

                            if ($connected) {
                                Notification::make()
                                    ->title('✅ Koneksi LAN berhasil!')
                                    ->body($mesin->nama . ' · IP: ' . $mesin->alamat_ip . ' · Port: ' . $mesin->port)
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('❌ Gagal terhubung ke ' . $mesin->nama)
                                    ->body('Pastikan mesin menyala dan kabel LAN terpasang.')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('❌ Error koneksi: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }
}

