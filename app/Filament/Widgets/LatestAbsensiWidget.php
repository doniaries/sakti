<?php

namespace App\Filament\Widgets;

use App\Models\LogAbsensi;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestAbsensiWidget extends BaseWidget
{
    use \BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = '5 Log Absensi Terakhir';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LogAbsensi::query()
                    ->with(['user', 'mesinAbsensi'])
                    ->when(auth()->user()->hasRole('pegawai'), fn($query) => $query->where('user_id', auth()->id()))
                    ->latest('waktu_absensi')
                    ->limit(5)
            )
            ->poll('5s')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('sumber')
                    ->label('Sumber')
                    ->getStateUsing(fn($record) => $record->mesin_absensi_id ? 'Mesin' : 'Aplikasi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Mesin' => 'success',
                        'Aplikasi' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('waktu_absensi')
                    ->label('Waktu Absen')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('mesinAbsensi.nama')
                    ->label('Mesin')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated(false);
    }
}
