<?php

namespace App\Filament\Resources\AbsensiMobiles;

use App\Filament\Resources\AbsensiMobiles\Pages\CreateAbsensiMobile;
use App\Filament\Resources\AbsensiMobiles\Pages\EditAbsensiMobile;
use App\Filament\Resources\AbsensiMobiles\Pages\ListAbsensiMobiles;
use App\Filament\Resources\AbsensiMobiles\Pages\ViewAbsensiMobile;
use App\Filament\Resources\AbsensiMobiles\Schemas\AbsensiMobileForm;
use App\Filament\Resources\AbsensiMobiles\Schemas\AbsensiMobileInfolist;
use App\Filament\Resources\AbsensiMobiles\Tables\AbsensiMobilesTable;
use App\Models\AbsensiMobile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AbsensiMobileResource extends Resource
{
    protected static ?string $model = AbsensiMobile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AbsensiMobileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AbsensiMobileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AbsensiMobilesTable::configure($table);
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
            'index' => ListAbsensiMobiles::route('/'),
            'create' => CreateAbsensiMobile::route('/create'),
            'view' => ViewAbsensiMobile::route('/{record}'),
            'edit' => EditAbsensiMobile::route('/{record}/edit'),
        ];
    }
}
