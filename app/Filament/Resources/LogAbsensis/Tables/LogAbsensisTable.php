<?php

namespace App\Filament\Resources\LogAbsensis\Tables;

use App\Models\LogAbsensi;
use App\Models\MesinAbsensi;
use App\Models\User;
use App\Services\AbsensiHarianService;
use App\Services\ZKTecoService;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Collection;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

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
                /*   TextColumn::make('mesinAbsensi.nama')
                    ->label('Mesin')
                    ->sortable()
                    ->searchable(), */
                /* TextColumn::make('nomor_absensi')
                    ->searchable(), */
                TextColumn::make('waktu_absensi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                /* TextColumn::make('perangkat')
                    ->searchable(), */
                TextColumn::make('metode_verifikasi')
                    ->searchable(),
                TextColumn::make('jenis')
                    ->searchable(),
                TextColumn::make('kode_log_asli')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('bulan_tahun')
                    ->form([
                        Select::make('bulan')
                            ->label('Bulan')
                            ->options([
                                '01' => 'Januari',
                                '02' => 'Februari',
                                '03' => 'Maret',
                                '04' => 'April',
                                '05' => 'Mei',
                                '06' => 'Juni',
                                '07' => 'Juli',
                                '08' => 'Agustus',
                                '09' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ]),
                        Select::make('tahun')
                            ->label('Tahun')
                            ->options(function () {
                                $years = [];
                                $currentYear = (int) date('Y');
                                for ($i = $currentYear - 5; $i <= $currentYear; $i++) {
                                    $years[$i] = $i;
                                }
                                return $years;
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['bulan'] ?? null,
                                fn(Builder $query, $bulan): Builder => $query->whereMonth('waktu_absensi', $bulan)
                            )
                            ->when(
                                $data['tahun'] ?? null,
                                fn(Builder $query, $tahun): Builder => $query->whereYear('waktu_absensi', $tahun)
                            );
                    })
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (Collection $records) {
                            $records->toQuery()->delete();
                        }),
                ]),
            ])
            ->headerActions([
                Action::make('tarik_log')
                    ->label('Tarik Log Absensi Mesin')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('tanggal_awal')
                            ->label('Dari Tanggal')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->default(now()->startOfMonth())
                            ->required(),
                        \Filament\Forms\Components\DatePicker::make('tanggal_akhir')
                            ->label('Sampai Tanggal')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->default(now()->endOfMonth())
                            ->required(),
                    ])
                    ->action(function (array $data) {
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

                            $tglAwal = Carbon::parse($data['tanggal_awal'])->startOfDay();
                            $tglAkhir = Carbon::parse($data['tanggal_akhir'])->endOfDay();

                            // Optimasi: Ambil semua user sekali saja
                            $usersByPin = User::query()->where('nomor_absensi', '!=', null)
                                ->get()
                                ->keyBy('nomor_absensi');

                            // Optimasi: Ambil log yang sudah ada dalam rentang tanggal
                            $existingLogs = LogAbsensi::whereBetween('waktu_absensi', [$tglAwal, $tglAkhir])
                                ->get()
                                ->map(fn($log) => $log->nomor_absensi . '|' . $log->waktu_absensi)
                                ->flip()
                                ->toArray();

                            $inserts = [];

                            foreach ($attendance as $log) {
                                $pin   = $log['id'];
                                $waktuCarbon = Carbon::parse($log['timestamp']);

                                // Skip jika tidak berada dalam rentang tanggal yang dipilih
                                if ($waktuCarbon->lt($tglAwal) || $waktuCarbon->gt($tglAkhir)) {
                                    continue;
                                }

                                $waktu = $waktuCarbon->format('Y-m-d H:i:s');
                                $user = $usersByPin->get($pin);
                                $key = $pin . '|' . $waktu;

                                // Simpan hanya jika log belum ada
                                if (!isset($existingLogs[$key]) && !isset($inserts[$key])) {
                                    $inserts[$key] = [
                                        'user_id'           => $user?->id,
                                        'mesin_absensi_id'  => $mesin->id,
                                        'nomor_absensi'     => $pin,
                                        'waktu_absensi'     => $waktu,
                                        'perangkat'         => 'mesin',
                                        'metode_verifikasi' => $log['type'] ?? null,
                                        'jenis'             => $log['state'] ?? null,
                                        'kode_log_asli'     => $log['uid'] ?? null,
                                        'created_at'        => now(),
                                        'updated_at'        => now(),
                                    ];
                                    $newLogs++;

                                    // Kumpulkan user_id + tanggal yang perlu diproses
                                    if ($user) {
                                        $tanggal = $waktuCarbon->format('Y-m-d');
                                        $tanggalDiproses[$user->id . '|' . $tanggal] = [
                                            'user_id' => $user->id,
                                            'tanggal' => $tanggal,
                                        ];
                                    }
                                }
                            }

                            // Insert batch agar jauh lebih cepat
                            foreach (array_chunk($inserts, 500) as $chunk) {
                                LogAbsensi::insert($chunk);
                            }

                            // Perbarui sinkronisasi_terakhir mesin
                            $mesin->update(['sinkronisasi_terakhir' => now()]);

                            // Proses log menjadi absensi_harian
                            $absensiService = new AbsensiHarianService();
                            $prosesCount = 0;
                            foreach ($tanggalDiproses as $item) {
                                $prosesCount += $absensiService->prosesLog($item['tanggal'], $item['user_id']);
                            }

                            $message = "✅ <strong>Berhasil menarik $newLogs log absensi baru.</strong><br>";
                            if ($prosesCount > 0) {
                                $message .= "🔄 $prosesCount data absensi harian diperbarui.<br>";
                            }
                            $message .= "⏱️ Waktu: " . now()->format('d/m/Y H:i:s');

                            Notification::make()
                                ->title('Hasil Tarik Log Absensi')
                                ->body($message)
                                ->success()
                                ->persistent()
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
                                $serverIp = getHostByName(getHostName());
                                Notification::make()
                                    ->title('✅ Koneksi LAN berhasil!')
                                    ->body($mesin->nama . ' · IP Mesin: ' . $mesin->alamat_ip . ' · Port: ' . $mesin->port . '<br>💻 IP Komputer Server: ' . $serverIp)
                                    ->success()
                                    ->persistent()
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
