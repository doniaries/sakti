<?php

declare(strict_types=1);

namespace App\Filament\Resources\BeritaAcaraBmd;

use App\Filament\Resources\BeritaAcaraBmd\Pages\CreateBeritaAcaraBmd;
use App\Filament\Resources\BeritaAcaraBmd\Pages\EditBeritaAcaraBmd;
use App\Filament\Resources\BeritaAcaraBmd\Pages\ListBeritaAcaraBmds;
use App\Filament\Resources\BeritaAcaraBmd\Schemas\BeritaAcaraBmdForm;
use App\Filament\Resources\BeritaAcaraBmd\Tables\BeritaAcaraBmdTable;
use App\Models\BeritaAcaraBmd;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BeritaAcaraBmdResource extends Resource
{
    protected static ?string $model = BeritaAcaraBmd::class;

    protected static ?string $slug = 'berita-acara-bmds';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $modelLabel = 'Berita Acara BMD';

    protected static ?string $pluralModelLabel = 'Berita Acara Aset (BMD)';

    protected static ?string $navigationLabel = 'Berita Acara BMD';

    protected static string|\UnitEnum|null $navigationGroup = 'Aset & Inventaris';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return BeritaAcaraBmdForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BeritaAcaraBmdTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBeritaAcaraBmds::route('/'),
            'create' => CreateBeritaAcaraBmd::route('/create'),
            'edit' => EditBeritaAcaraBmd::route('/{record}/edit'),
        ];
    }
}
