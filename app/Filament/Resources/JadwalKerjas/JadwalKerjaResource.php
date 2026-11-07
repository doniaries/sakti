<?php

namespace App\Filament\Resources\JadwalKerjas;

use App\Filament\Resources\JadwalKerjas\Pages\CreateJadwalKerja;
use App\Filament\Resources\JadwalKerjas\Pages\EditJadwalKerja;
use App\Filament\Resources\JadwalKerjas\Pages\ListJadwalKerjas;
use App\Filament\Resources\JadwalKerjas\Schemas\JadwalKerjaForm;
use App\Filament\Resources\JadwalKerjas\Tables\JadwalKerjasTable;
use App\Models\JadwalKerja;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JadwalKerjaResource extends Resource
{
    protected static ?string $model = JadwalKerja::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $modelLabel = 'Jadwal Kerja';
    protected static ?string $pluralModelLabel = 'Jadwal / Shift Kerja';

    protected static string|\UnitEnum|null $navigationGroup = 'Kepegawaian';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return JadwalKerjaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JadwalKerjasTable::configure($table);
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
            'index' => ListJadwalKerjas::route('/'),
            'create' => CreateJadwalKerja::route('/create'),
            'edit' => EditJadwalKerja::route('/{record}/edit'),
        ];
    }
}
