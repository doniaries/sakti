<?php

namespace App\Filament\Resources\Reputasis;

use App\Filament\Resources\Reputasis\Pages\CreateReputasi;
use App\Filament\Resources\Reputasis\Pages\EditReputasi;
use App\Filament\Resources\Reputasis\Pages\ListReputasis;
use App\Filament\Resources\Reputasis\Schemas\ReputasiForm;
use App\Filament\Resources\Reputasis\Tables\ReputasisTable;
use App\Models\Reputasi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReputasiResource extends Resource
{
    protected static ?string $model = Reputasi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ReputasiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReputasisTable::configure($table);
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
            'index' => ListReputasis::route('/'),
            'create' => CreateReputasi::route('/create'),
            'edit' => EditReputasi::route('/{record}/edit'),
        ];
    }
}
