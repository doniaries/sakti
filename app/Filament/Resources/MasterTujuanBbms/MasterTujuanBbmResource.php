<?php

namespace App\Filament\Resources\MasterTujuanBbms;

use App\Filament\Resources\MasterTujuanBbms\Pages\CreateMasterTujuanBbm;
use App\Filament\Resources\MasterTujuanBbms\Pages\EditMasterTujuanBbm;
use App\Filament\Resources\MasterTujuanBbms\Pages\ListMasterTujuanBbms;
use App\Filament\Resources\MasterTujuanBbms\Schemas\MasterTujuanBbmForm;
use App\Filament\Resources\MasterTujuanBbms\Tables\MasterTujuanBbmsTable;
use App\Models\MasterTujuanBbm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MasterTujuanBbmResource extends Resource
{
    protected static ?string $model = MasterTujuanBbm::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $modelLabel = 'Standar Kuota BBM';

    protected static ?string $pluralModelLabel = 'Standar Kuota BBM';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Keuangan';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return MasterTujuanBbmForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterTujuanBbmsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterTujuanBbms::route('/'),
            'create' => CreateMasterTujuanBbm::route('/create'),
            'edit' => EditMasterTujuanBbm::route('/{record}/edit'),
        ];
    }
}
