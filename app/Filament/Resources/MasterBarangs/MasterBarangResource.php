<?php

namespace App\Filament\Resources\MasterBarangs;

use App\Filament\Resources\MasterBarangs\Pages\CreateMasterBarang;
use App\Filament\Resources\MasterBarangs\Pages\EditMasterBarang;
use App\Filament\Resources\MasterBarangs\Pages\ListMasterBarangs;
use App\Filament\Resources\MasterBarangs\Schemas\MasterBarangForm;
use App\Filament\Resources\MasterBarangs\Tables\MasterBarangsTable;
use App\Models\MasterBarang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MasterBarangResource extends Resource
{
    protected static ?string $model = MasterBarang::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $modelLabel = 'Komponen Barang';

    protected static ?string $pluralModelLabel = 'Komponen Barang';

    protected static ?string $navigationLabel = 'Komponen Barang';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return MasterBarangForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterBarangsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterBarangs::route('/'),
            'create' => CreateMasterBarang::route('/create'),
            'edit' => EditMasterBarang::route('/{record}/edit'),
        ];
    }
}
