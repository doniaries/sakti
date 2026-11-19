<?php

declare(strict_types=1);

namespace App\Filament\Resources\BukuInventaris\Tables;

use App\Models\BukuInventaris;
use App\Support\FormatHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
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
                    'photo',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ]);
            })
            ->columns([
                /* TextColumn::make('nomor_urut')
                    ->label('Urut')
                    ->sortable()
                    ->alignCenter()
                    ->width('60px'), */
                TextColumn::make('nama_barang')
                    ->label('Nama / Jenis Barang')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->wrap()
                    ->extraHeaderAttributes([
                        'class' => 'sticky left-0 z-20 bg-gray-50 dark:bg-gray-800 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]',
                        'style' => 'position: sticky; left: 0; z-index: 20;',
                    ])
                    ->extraCellAttributes([
                        'class' => 'sticky left-0 z-10 bg-white dark:bg-gray-900 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]',
                        'style' => 'position: sticky; left: 0; z-index: 10;',
                    ]),
                ImageColumn::make('photo')
                    ->label('Foto')
                    ->disk('public')
                    ->square()
                    ->toggleable(),
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



                TextColumn::make('merk_type')
                    ->label('Merk / Type')
                    ->searchable()
                    ->placeholder('-')
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('nomor_sertifikat_pabrik_chasis_mesin')
                    ->label('No. Sertifikat / Pabrik / Chasis / Mesin')
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
                    ->color(fn(?string $state): string => match (strtolower(trim((string) $state))) {
                        'baik', 'b' => 'success',
                        'kurang baik', 'kb' => 'warning',
                        'rusak berat', 'rb' => 'danger',
                        default => 'gray',
                    })
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('jumlah_barang')
                    ->label('Jml')
                    ->formatStateUsing(fn($state): string => number_format((int) $state, 0, ',', '.'))
                    ->alignCenter()
                    ->sortable()
                    ->width('60px')
                    ->summarize(
                        Sum::make()
                            ->label('Total Barang')
                            ->formatStateUsing(fn($state): string => number_format((int) $state, 0, ',', '.'))
                    ),

                TextColumn::make('harga')
                    ->label('Harga Perolehan (Rp)')
                    ->formatStateUsing(fn($state): string => FormatHelper::rupiah((float) $state))
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold')
                    ->summarize(
                        Sum::make()
                            ->label('Total Nilai Aset')
                            ->formatStateUsing(fn($state): string => FormatHelper::rupiah((float) $state))
                    ),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->searchable()
                    ->placeholder('-')
                    ->wrap()
                    ->toggleable(),

                FormatHelper::dateTimeColumn('created_at', 'Waktu Dibuat')
                    ->toggleable(isToggledHiddenByDefault: true),

                FormatHelper::dateTimeColumn('updated_at', 'Waktu Diperbarui')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nomor_urut', 'asc')
            ->emptyStateHeading('Belum Ada Data Inventaris')
            ->emptyStateDescription('Data buku inventaris gabungan belum tersedia.')
            ->filters([
                SelectFilter::make('tahun_pembelian')
                    ->label('Tahun Perolehan')
                    ->options(function (): array {
                        return BukuInventaris::query()
                            ->whereNotNull('tahun_pembelian')
                            ->distinct()
                            ->orderByDesc('tahun_pembelian')
                            ->pluck('tahun_pembelian', 'tahun_pembelian')
                            ->toArray();
                    }),

                SelectFilter::make('keadaan_barang')
                    ->label('Keadaan Barang')
                    ->options([
                        'Baik' => 'Baik',
                        'Kurang Baik' => 'Kurang Baik',
                        'Rusak Berat' => 'Rusak Berat',
                    ]),

                SelectFilter::make('asal_perolehan')
                    ->label('Asal Perolehan')
                    ->options([
                        'Pembelian' => 'Pembelian',
                        'Mutasi' => 'Mutasi',
                        'Hibah' => 'Hibah',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
