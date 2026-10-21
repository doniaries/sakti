<?php

namespace App\Filament\Resources\JenisIzins;

use App\Filament\Resources\JenisIzins\Pages\CreateJenisIzin;
use App\Filament\Resources\JenisIzins\Pages\EditJenisIzin;
use App\Filament\Resources\JenisIzins\Pages\ListJenisIzins;
use App\Filament\Resources\JenisIzins\Schemas\JenisIzinForm;
use App\Filament\Resources\JenisIzins\Tables\JenisIzinsTable;
use App\Models\JenisIzin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JenisIzinResource extends Resource
{
    protected static ?string $model = JenisIzin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $modelLabel = 'Tipe Izin & Cuti';
    protected static ?string $pluralModelLabel = 'Tipe Izin & Cuti';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return JenisIzinForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JenisIzinsTable::configure($table);
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
            'index' => ListJenisIzins::route('/'),
            'create' => CreateJenisIzin::route('/create'),
            'edit' => EditJenisIzin::route('/{record}/edit'),
        ];
    }
}
