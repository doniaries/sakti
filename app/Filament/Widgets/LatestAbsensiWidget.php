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
                    ->latest('timestamp')
                    ->limit(5)
            )
            ->poll('5s')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Hadir' => 'success',
                        'Pulang' => 'warning',
                        'Izin' => 'info',
                        'Sakit' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('timestamp')
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
