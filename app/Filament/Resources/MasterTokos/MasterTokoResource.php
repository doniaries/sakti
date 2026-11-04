<?php

namespace App\Filament\Resources\MasterTokos;

use App\Filament\Resources\MasterTokos\Pages\CreateMasterToko;
use App\Filament\Resources\MasterTokos\Pages\EditMasterToko;
use App\Filament\Resources\MasterTokos\Pages\ListMasterTokos;
use App\Filament\Resources\MasterTokos\Schemas\MasterTokoForm;
use App\Filament\Resources\MasterTokos\Tables\MasterTokosTable;
use App\Models\MasterToko;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MasterTokoResource extends Resource
{
    protected static ?string $model = MasterToko::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $modelLabel = 'Data Toko / Rekanan';

    protected static ?string $pluralModelLabel = 'Data Toko / Rekanan';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Keuangan';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return MasterTokoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterTokosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterTokos::route('/'),
            'create' => CreateMasterToko::route('/create'),
            'edit' => EditMasterToko::route('/{record}/edit'),
        ];
    }
}
