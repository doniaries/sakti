<?php

namespace App\Filament\Resources\MesinAbsensis;

use App\Filament\Resources\MesinAbsensis\Pages\CreateMesinAbsensi;
use App\Filament\Resources\MesinAbsensis\Pages\EditMesinAbsensi;
use App\Filament\Resources\MesinAbsensis\Pages\ListMesinAbsensis;
use App\Filament\Resources\MesinAbsensis\Pages\ViewMesinAbsensi;
use App\Filament\Resources\MesinAbsensis\Schemas\MesinAbsensiForm;
use App\Filament\Resources\MesinAbsensis\Schemas\MesinAbsensiInfolist;
use App\Filament\Resources\MesinAbsensis\Tables\MesinAbsensisTable;
use App\Models\MesinAbsensi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MesinAbsensiResource extends Resource
{
    protected static ?string $model = MesinAbsensi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return MesinAbsensiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MesinAbsensiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MesinAbsensisTable::configure($table);
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
            'index' => ListMesinAbsensis::route('/'),
            'create' => CreateMesinAbsensi::route('/create'),
            'view' => ViewMesinAbsensi::route('/{record}'),
            'edit' => EditMesinAbsensi::route('/{record}/edit'),
        ];
    }
}
