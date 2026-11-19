<?php

declare(strict_types=1);

namespace App\Filament\Resources\BukuInventaris\Tables;

use App\Models\BukuInventaris;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BukuInventarisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->select([
                    'id',
                    'nomor_urut',
                    'kode_barang',
                    'register',
                    'nama_barang',
                    'merk_type',
                    'nomor_sertifikat_pabrik_chasis_mesin',
                    'bahan',
                    'asal_perolehan',
                    'tahun_pembelian',
                    'ukuran_konstruksi',
                    'satuan',
                    'keadaan_barang',
                    'jumlah_barang',
                    'harga',
                    'keterangan',
                    'created_at',
                ]);
            })
            ->columns([
                TextColumn::make('nomor_urut')
                    ->label('Urut')
                    ->sortable()
                    ->alignCenter()
                    ->width('60px'),

                TextColumn::make('kode_barang')
                    ->label('Kode Barang')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->width('130px'),

                TextColumn::make('register')
                    ->label('Register')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->badge()
                    ->color('gray')
                    ->width('110px'),

                TextColumn::make('nama_barang')
                    ->label('Nama / Jenis Barang')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->wrap(),

                TextColumn::make('merk_type')
                    ->label('Merk / Type')
                    ->searchable()
                    ->placeholder('-')
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('nomor_sertifikat_pabrik_chasis_mesin')
                    ->label('No. Sertifikat/Mesin')
                    ->searchable()
                    ->placeholder('-')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bahan')
                    ->label('Bahan')
                    ->placeholder('-')
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('asal_perolehan')
                    ->label('Asal Usul')
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tahun_pembelian')
                    ->label('Tahun')
                    ->sortable()
                    ->alignCenter()
                    ->badge(),

                TextColumn::make('ukuran_konstruksi')
                    ->label('Ukuran / Konstruksi')
                    ->formatStateUsing(function ($state, BukuInventaris $record): string {
                        $parts = array_filter([$record->ukuran_konstruksi, $record->satuan]);
                        return !empty($parts) ? implode(' ', $parts) : '-';
                    })
                    ->alignCenter()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('keadaan_barang')
                    ->label('Keadaan')
                    ->badge()
                    ->colors([
                        'success' => 'Baik',
                        'warning' => 'Kurang Baik',
                        'danger' => 'Rusak Berat',
                    ])
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('jumlah_barang')
                    ->label('Jml')
                    ->alignCenter()
                    ->sortable()
                    ->width('50px'),

                TextColumn::make('harga')
                    ->label('Harga (Rp)')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold'),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->searchable()
                    ->placeholder('-')
                    ->wrap()
                    ->toggleable(),
            ])
            ->defaultSort('nomor_urut', 'asc')
            ->filters([
                SelectFilter::make('tahun_pembelian')
                    ->label('Filter Tahun Perolehan')
                    ->options(function (): array {
                        return BukuInventaris::query()
                            ->whereNotNull('tahun_pembelian')
                            ->distinct()
                            ->orderByDesc('tahun_pembelian')
                            ->pluck('tahun_pembelian', 'tahun_pembelian')
                            ->toArray();
                    }),

                SelectFilter::make('keadaan_barang')
                    ->label('Filter Keadaan')
                    ->options([
                        'Baik' => 'Baik',
                        'Kurang Baik' => 'Kurang Baik',
                        'Rusak Berat' => 'Rusak Berat',
                    ]),

                SelectFilter::make('asal_perolehan')
                    ->label('Filter Asal Perolehan')
                    ->options([
                        'Pembelian' => 'Pembelian',
                        'Mutasi' => 'Mutasi',
                        'Hibah' => 'Hibah',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
