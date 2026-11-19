<?php

declare(strict_types=1);

namespace App\Filament\Resources\BukuInventaris;

use App\Filament\Resources\BukuInventaris\Pages\CreateBukuInventaris;
use App\Filament\Resources\BukuInventaris\Pages\EditBukuInventaris;
use App\Filament\Resources\BukuInventaris\Pages\ListBukuInventaris;
use App\Filament\Resources\BukuInventaris\Schemas\BukuInventarisForm;
use App\Filament\Resources\BukuInventaris\Tables\BukuInventarisTable;
use App\Models\BukuInventaris;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BukuInventarisResource extends Resource
{
    protected static ?string $model = BukuInventaris::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $modelLabel = 'Buku Inventaris';

    protected static ?string $pluralModelLabel = 'Buku Inventaris Gabungan';

    protected static ?string $navigationLabel = 'Buku Inventaris';

    protected static string|\UnitEnum|null $navigationGroup = 'Aset & Inventaris';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return BukuInventarisForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BukuInventarisTable::configure($table);
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\BukuInventaris\Widgets\BukuInventarisOverviewWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBukuInventaris::route('/'),
            'create' => CreateBukuInventaris::route('/create'),
            'edit' => EditBukuInventaris::route('/{record}/edit'),
        ];
    }
}
