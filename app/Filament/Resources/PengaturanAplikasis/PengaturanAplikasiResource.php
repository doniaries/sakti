<?php

namespace App\Filament\Resources\PengaturanAplikasis;

use App\Filament\Resources\PengaturanAplikasis\Pages\CreatePengaturanAplikasi;
use App\Filament\Resources\PengaturanAplikasis\Pages\EditPengaturanAplikasi;
use App\Filament\Resources\PengaturanAplikasis\Pages\ListPengaturanAplikasis;
use App\Filament\Resources\PengaturanAplikasis\Schemas\PengaturanAplikasiForm;
use App\Filament\Resources\PengaturanAplikasis\Tables\PengaturanAplikasisTable;
use App\Models\PengaturanAplikasi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PengaturanAplikasiResource extends Resource
{
    protected static ?string $model = PengaturanAplikasi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $modelLabel = 'Konfigurasi Sistem';
    protected static ?string $pluralModelLabel = 'Konfigurasi Sistem';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nama_aplikasi';

    public static function canCreate(): bool
    {
        return PengaturanAplikasi::count() === 0;
    }

    public static function form(Schema $schema): Schema
    {
        return PengaturanAplikasiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengaturanAplikasisTable::configure($table);
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
            'index' => ListPengaturanAplikasis::route('/'),
            'create' => CreatePengaturanAplikasi::route('/create'),
            'edit' => EditPengaturanAplikasi::route('/{record}/edit'),
        ];
    }
}
