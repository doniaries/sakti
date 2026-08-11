<?php

namespace App\Filament\Resources\MesinAbsensis\Tables;

use App\Services\ZKTecoService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class MesinAbsensisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('merek')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('model')
                    ->searchable(),
                TextColumn::make('alamat_ip')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('port')
                    ->sortable(),
                TextColumn::make('nomor_seri')
                    ->searchable(),
                TextColumn::make('jenis_koneksi')
                    ->sortable(),
                ToggleColumn::make('aktif')
                    ->sortable(),
                TextColumn::make('sinkronisasi_terakhir')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('test_koneksi')
                    ->label('Test Koneksi')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function ($record) {
                        try {
                            $service = new ZKTecoService($record->alamat_ip, $record->port);
                            $connected = $service->connect();
                            $service->disconnect();

                            if ($connected) {
                                $serverIp = getHostByName(getHostName());
                                
                                Notification::make()
                                    ->title('✅ Koneksi LAN ke ' . $record->nama . ' berhasil!')
                                    ->body('IP Mesin: ' . $record->alamat_ip . ' · Port: ' . $record->port . '<br>💻 IP Komputer Server: ' . $serverIp)
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('❌ Gagal terhubung ke ' . $record->nama)
                                    ->body('Pastikan mesin menyala, kabel LAN terpasang, dan IP sesuai.')
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
