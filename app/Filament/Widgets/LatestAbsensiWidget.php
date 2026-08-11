<?php

namespace App\Filament\Widgets;

use App\Models\LogAbsensi;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestAbsensiWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = '5 Log Absensi Terakhir';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LogAbsensi::query()
                    ->with(['user', 'mesinAbsensi'])
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
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Mesin' => 'success',
                        'Manual' => 'warning',
                        'Mobile' => 'info',
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
