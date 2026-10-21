<?php

namespace App\Filament\Resources\RencanaAksis;

use App\Filament\Resources\RencanaAksis\Pages\CreateRencanaAksi;
use App\Filament\Resources\RencanaAksis\Pages\EditRencanaAksi;
use App\Filament\Resources\RencanaAksis\Pages\ListRencanaAksis;
use App\Filament\Resources\RencanaAksis\Schemas\RencanaAksiForm;
use App\Filament\Resources\RencanaAksis\Tables\RencanaAksisTable;
use App\Models\RencanaAksi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RencanaAksiResource extends Resource
{
    protected static ?string $model = RencanaAksi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'rencana_aksi';

    protected static string|\UnitEnum|null $navigationGroup = 'LHK';
    protected static ?string $modelLabel = 'Rencana Aksi';
    protected static ?string $pluralModelLabel = 'Data Rencana Aksi';
    protected static ?int    $navigationSort     = 2;

    public static function form(Schema $schema): Schema
    {
        return RencanaAksiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RencanaAksisTable::configure($table);
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
            'index' => ListRencanaAksis::route('/'),
            'create' => CreateRencanaAksi::route('/create'),
            'edit' => EditRencanaAksi::route('/{record}/edit'),
        ];
    }
}
