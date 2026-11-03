<?php

namespace App\Filament\Resources\MasterBanks;

use App\Filament\Resources\MasterBanks\Pages\CreateMasterBank;
use App\Filament\Resources\MasterBanks\Pages\EditMasterBank;
use App\Filament\Resources\MasterBanks\Pages\ListMasterBanks;
use App\Filament\Resources\MasterBanks\Schemas\MasterBankForm;
use App\Filament\Resources\MasterBanks\Tables\MasterBanksTable;
use App\Models\MasterBank;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MasterBankResource extends Resource
{
    protected static ?string $model = MasterBank::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $modelLabel = 'Data Bank';

    protected static ?string $pluralModelLabel = 'Data Bank';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Keuangan';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return MasterBankForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterBanksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterBanks::route('/'),
            'create' => CreateMasterBank::route('/create'),
            'edit' => EditMasterBank::route('/{record}/edit'),
        ];
    }
}
