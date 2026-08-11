<?php

namespace App\Filament\Resources\Lhks;

use App\Filament\Resources\Lhks\Pages;
use App\Filament\Resources\Lhks\Schemas\LhkForm;
use App\Models\Lhk;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LhkResource extends Resource
{
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'LHK';
    protected static ?string $model = Lhk::class;
    protected static ?string $modelLabel = 'Laporan LHK';
    protected static ?string $pluralModelLabel = 'Laporan LHK';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && $user->hasRole('Pegawai')) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return LhkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user', 'pekerjaans', 'logAbsensiMasuk', 'logAbsensiPulang']))
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('logAbsensiMasuk.waktu_absensi')
                    ->label('Masuk')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('logAbsensiPulang.waktu_absensi')
                    ->label('Pulang')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Hadir'      => 'success',
                        'Sakit'      => 'warning',
                        'Izin'       => 'danger',
                        'Dinas Luar' => 'info',
                        default      => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_approved')
                    ->boolean()
                    ->label('Approved?'),
            ])
            ->filters([
                //
            ])
            ->emptyStateActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLhks::route('/'),
            'create' => Pages\CreateLhk::route('/create'),
            'edit'   => Pages\EditLhk::route('/{record}/edit'),
        ];
    }
}
