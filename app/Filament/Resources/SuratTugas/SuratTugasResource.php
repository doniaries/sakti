<?php

declare(strict_types=1);

namespace App\Filament\Resources\SuratTugas;

use App\Filament\Resources\SuratTugas\Pages\CreateSuratTugas;
use App\Filament\Resources\SuratTugas\Pages\EditSuratTugas;
use App\Filament\Resources\SuratTugas\Pages\ListSuratTugas;
use App\Filament\Resources\SuratTugas\Schemas\SuratTugasForm;
use App\Filament\Resources\SuratTugas\Tables\SuratTugasTable;
use App\Models\SuratTugas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SuratTugasResource extends Resource
{
    protected static ?string $model = SuratTugas::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Persuratan';

    protected static ?string $navigationLabel = 'Surat Tugas';

    protected static ?string $modelLabel = 'Surat Tugas';

    protected static ?string $pluralModelLabel = 'Surat Tugas';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SuratTugasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuratTugasTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['penandatangan'])
            ->withCount('pegawais');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuratTugas::route('/'),
            'create' => CreateSuratTugas::route('/create'),
            'edit' => EditSuratTugas::route('/{record}/edit'),
        ];
    }
}
