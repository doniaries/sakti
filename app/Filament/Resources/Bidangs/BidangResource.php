<?php

namespace App\Filament\Resources\Bidangs;

use App\Filament\Resources\Bidangs\Pages\CreateBidang;
use App\Filament\Resources\Bidangs\Pages\EditBidang;
use App\Filament\Resources\Bidangs\Pages\ListBidangs;
use App\Filament\Resources\Bidangs\Schemas\BidangForm;
use App\Filament\Resources\Bidangs\Tables\BidangsTable;
use App\Models\Bidang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BidangResource extends Resource
{
    protected static ?string $model = Bidang::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $modelLabel = 'Bidang';

    protected static ?string $pluralModelLabel = 'Bidang';
    protected static string|\UnitEnum|null $navigationGroup = 'Kepegawaian';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return BidangForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BidangsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubBidangsRelationManager::class,
            RelationManagers\PegawaiRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBidangs::route('/'),
            'create' => CreateBidang::route('/create'),
            'edit' => EditBidang::route('/{record}/edit'),
        ];
    }
}
