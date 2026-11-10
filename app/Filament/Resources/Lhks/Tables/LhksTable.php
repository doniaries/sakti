<?php

namespace App\Filament\Resources\Lhks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;

class LhksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->modifyQueryUsing(fn($query) => $query->with(['user', 'pekerjaans', 'logAbsensiMasuk', 'logAbsensiPulang']))
            ->heading(new \Illuminate\Support\HtmlString('
                <style>
                    @media (min-width: 768px) {
                        .fi-ta-filters .fi-ta-filters-wrapper form {
                            display: flex;
                            align-items: flex-end;
                            gap: 1rem;
                        }
                        .fi-ta-filters .fi-ta-filters-wrapper form > .grid {
                            flex: 1;
                        }
                        .fi-ta-filters .fi-ta-filters-wrapper form > .flex.items-center {
                            margin-bottom: 0.25rem;
                        }
                    }
                </style>
            '))
            ->stickyableColumns()
            ->columns([
                TextColumn::make('tanggal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable()
                    ->sticky(),
                TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->sortable()
                    ->searchable()
                    ->sticky(),
                TextColumn::make('logAbsensiMasuk.waktu_absensi')
                    ->label('Masuk')
                    ->time('H:i'),
                TextColumn::make('logAbsensiPulang.waktu_absensi')
                    ->label('Pulang')
                    ->time('H:i'),
                TextColumn::make('status_kehadiran')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'Hadir'      => 'success',
                        'Sakit'      => 'warning',
                        'Izin'       => 'danger',
                        'Dinas Luar' => 'info',
                        default      => 'gray',
                    }),
                IconColumn::make('is_approved')
                    ->boolean()
                    ->label('Approved?'),
            ])
            ->filters([
                SelectFilter::make('bulan')
                    ->label('Filter Bulan')
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
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $value): Builder => $query->whereMonth('tanggal', $value)
                        );
                    }),

                SelectFilter::make('tahun')
                    ->label('Filter Tahun')
                    ->options(function () {
                        $years = [];
                        $currentYear = date('Y');
                        for ($i = $currentYear - 1; $i <= $currentYear + 2; $i++) {
                            $years[$i] = $i;
                        }
                        return $years;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $value): Builder => $query->whereYear('tanggal', $value)
                        );
                    }),

                Filter::make('tanggal_range')
                    ->form([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->displayFormat('d/m/Y')
                            ->native(false),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->displayFormat('d/m/Y')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari_tanggal'] ?? null) {
                            $indicators['dari_tanggal'] = 'Dari: ' . \Carbon\Carbon::parse($data['dari_tanggal'])->format('d M Y');
                        }
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators['sampai_tanggal'] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai_tanggal'])->format('d M Y');
                        }
                        return $indicators;
                    })
                    ->columns(2),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->defaultSort('tanggal', 'desc')
            ->recordActions([
                EditAction::make()

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
