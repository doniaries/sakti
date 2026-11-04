<?php

declare(strict_types=1);

namespace App\Filament\Resources\StokBarangs\Tables;

use App\Models\Bidang;
use App\Models\StokBarang;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StokBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query, Table $table): Builder {
                $query->with(['bidang', 'masterBarang.kodeRekening', 'masterBarang.satuan']);

                /** @var \App\Models\User|null $user */
                $user = Auth::user();
                $canViewAllBidang = $user !== null && $user->can('viewAny', Bidang::class);

                if (! $canViewAllBidang && $user !== null) {
                    $userBidangId = filled($user->bidang_id)
                        ? (int) $user->bidang_id
                        : (int) (Bidang::where('kepala_id', $user->id)->value('id') ?? 0);

                    if ($userBidangId > 0) {
                        $query->where('bidang_id', $userBidangId);
                    }
                }

                $selectedBidang = request()->query('bidang');
                if (filled($selectedBidang)) {
                    $query->where('bidang_id', (int) $selectedBidang);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('bidang.nama')
                    ->label('Bidang Pengelola')
                    ->badge()
                    ->color(fn (?string $state): string => Bidang::badgeColor($state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('masterBarang.kode_barang')
                    ->label('Kode Barang')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('masterBarang.nama_barang')
                    ->label('Nama Barang Persediaan')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('masterBarang.kodeRekening.uraian')
                    ->label('Rekening Belanja')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('stok_tersedia')
                    ->label('Sisa Stok Fisik')
                    ->formatStateUsing(fn (StokBarang $record): string => "{$record->stok_tersedia} " . ($record->masterBarang?->satuan?->nama ?? ''))
                    ->badge()
                    ->color(function (StokBarang $record): string {
                        if ($record->stok_tersedia <= 0) {
                            return 'danger';
                        }
                        if ($record->stok_tersedia <= 3) {
                            return 'warning';
                        }
                        return 'success';
                    })
                    ->icon(function (StokBarang $record): string {
                        if ($record->stok_tersedia <= 0) {
                            return 'heroicon-m-x-circle';
                        }
                        if ($record->stok_tersedia <= 3) {
                            return 'heroicon-m-exclamation-triangle';
                        }
                        return 'heroicon-m-check-circle';
                    })
                    ->sortable(),

                TextColumn::make('status_kondisi')
                    ->label('Kondisi')
                    ->state(function (StokBarang $record): string {
                        if ($record->stok_tersedia <= 0) {
                            return 'Habis';
                        }
                        if ($record->stok_tersedia <= 3) {
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

                TextColumn::make('masterBarang.harga_satuan')
                    ->label('Harga Standar')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nilai_persediaan')
                    ->label('Estimasi Nilai')
                    ->state(fn (StokBarang $record): float => (float) ($record->stok_tersedia * ($record->masterBarang?->harga_satuan ?? 0)))
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Pembaruan Terakhir')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('bidang_id')
                    ->relationship('bidang', 'nama')
                    ->label('Filter Bidang'),
            ])
            ->actions([
                Action::make('sesuaikan_stok')
                    ->label('Update Stok')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->modalHeading(fn (StokBarang $record): string => "Update Stok: {$record->masterBarang?->nama_barang}")
                    ->modalDescription(fn (StokBarang $record): string => "Bidang {$record->bidang?->nama} — masukkan jumlah stok fisik riil.")
                    ->form([
                        TextInput::make('stok_tersedia')
                            ->label('Jumlah Stok Fisik Baru')
                            ->numeric()
                            ->default(fn (StokBarang $record): int => (int) $record->stok_tersedia)
                            ->required()
                            ->minValue(0)
                            ->suffix(fn (StokBarang $record): string => $record->masterBarang?->satuan?->nama ?? 'Unit'),
                    ])
                    ->action(function (StokBarang $record, array $data): void {
                        $record->update([
                            'stok_tersedia' => (int) $data['stok_tersedia'],
                        ]);

                        Notification::make()
                            ->title('Stok Berhasil Diperbarui')
                            ->body("Stok {$record->masterBarang?->nama_barang} di {$record->bidang?->nama} kini menjadi {$data['stok_tersedia']}.")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tambah Stok Persediaan')
                    ->icon('heroicon-m-plus'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

