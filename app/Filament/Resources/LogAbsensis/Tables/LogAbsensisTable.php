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
            ->deferLoading()
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
            ->defaultSort('waktu_absensi', 'desc')
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
            ->headerActions([]);
    }
}
