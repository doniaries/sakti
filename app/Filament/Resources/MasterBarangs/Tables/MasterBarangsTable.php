<?php

namespace App\Filament\Resources\MasterBarangs\Tables;

use App\Models\Bidang;
use App\Models\MasterBarang;
use App\Models\StokBarang;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->with(['kodeRekening', 'satuan', 'stokBarangs.bidang']);
            })
            ->columns([
                TextColumn::make('kode_barang')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama_barang')
                    ->label('Nama Barang Persediaan')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('kodeRekening.uraian')
                    ->label('Rekening Belanja')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('stok_tersedia')
                    ->label(function (): string {
                        $bidangId = request()->query('bidang');
                        if (filled($bidangId)) {
                            $bidang = Bidang::find($bidangId);
                            return $bidang ? "Stok ({$bidang->nama})" : 'Stok Bidang';
                        }
                        return 'Total Stok Dinas';
                    })
                    ->state(function (MasterBarang $record): string {
                        $bidangId = request()->query('bidang');
                        $satuan = $record->satuan?->nama ?? 'Unit';

                        if (filled($bidangId)) {
                            $stok = $record->stokBarangs->firstWhere('bidang_id', (int) $bidangId)?->stok_tersedia ?? 0;
                            return "{$stok} {$satuan}";
                        }

                        $total = (int) $record->stokBarangs->sum('stok_tersedia');
                        return "{$total} {$satuan}";
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        $qty = (int) filter_var($state, FILTER_SANITIZE_NUMBER_INT);
                        if ($qty <= 0) {
                            return 'danger';
                        }
                        if ($qty <= 3) {
                            return 'warning';
                        }
                        return 'success';
                    })
                    ->icon(function (string $state): string {
                        $qty = (int) filter_var($state, FILTER_SANITIZE_NUMBER_INT);
                        if ($qty <= 0) {
                            return 'heroicon-m-x-circle';
                        }
                        if ($qty <= 3) {
                            return 'heroicon-m-exclamation-triangle';
                        }
                        return 'heroicon-m-check-circle';
                    })
                    ->sortable(false),

                TextColumn::make('status_stok')
                    ->label('Kondisi')
                    ->state(function (MasterBarang $record): string {
                        $bidangId = request()->query('bidang');
                        $stok = filled($bidangId)
                            ? ($record->stokBarangs->firstWhere('bidang_id', (int) $bidangId)?->stok_tersedia ?? 0)
                            : (int) $record->stokBarangs->sum('stok_tersedia');

                        if ($stok <= 0) {
                            return 'Habis';
                        }
                        if ($stok <= 3) {
                            return 'Kritis';
                        }
                        return 'Aman';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Habis' => 'danger',
                        'Kritis' => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('harga_satuan')
                    ->label('Harga Standar')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('satuan.nama')
                    ->label('Satuan')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('sesuaikan_stok')
                    ->label('Update Stok')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->modalHeading(fn (MasterBarang $record): string => "Sesuaikan Stok: {$record->nama_barang}")
                    ->modalDescription('Perbarui jumlah stok fisik barang persediaan untuk bidang pengelola.')
                    ->form([
                        Select::make('bidang_id')
                            ->label('Bidang Pengelola')
                            ->options(fn () => Bidang::pluck('nama', 'id')->all())
                            ->default(fn () => request()->query('bidang') ?? Bidang::first()?->id)
                            ->required(),
                        TextInput::make('stok_tersedia')
                            ->label('Jumlah Stok Fisik Baru')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->suffix(fn (MasterBarang $record): string => $record->satuan?->nama ?? 'Unit'),
                    ])
                    ->action(function (MasterBarang $record, array $data): void {
                        StokBarang::updateOrCreate(
                            [
                                'bidang_id' => $data['bidang_id'],
                                'master_barang_id' => $record->id,
                            ],
                            [
                                'stok_tersedia' => (int) $data['stok_tersedia'],
                            ]
                        );

                        Notification::make()
                            ->title('Stok Berhasil Diperbarui')
                            ->body("Stok {$record->nama_barang} telah disimpan.")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tambah Master Barang')
                    ->icon('heroicon-m-plus'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

