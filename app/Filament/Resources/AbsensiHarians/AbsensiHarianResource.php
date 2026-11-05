<?php

namespace App\Filament\Resources\AbsensiHarians;

use App\Filament\Resources\AbsensiHarians\Pages\CreateAbsensiHarian;
use App\Filament\Resources\AbsensiHarians\Pages\EditAbsensiHarian;
use App\Filament\Resources\AbsensiHarians\Pages\ListAbsensiHarians;
use App\Filament\Resources\AbsensiHarians\Pages\ViewAbsensiHarian;
use App\Filament\Resources\AbsensiHarians\Schemas\AbsensiHarianForm;
use App\Filament\Resources\AbsensiHarians\Schemas\AbsensiHarianInfolist;
use App\Filament\Resources\AbsensiHarians\Tables\AbsensiHariansTable;
use App\Models\AbsensiHarian;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AbsensiHarianResource extends Resource
{
    protected static ?string $model = AbsensiHarian::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Absensi';
    protected static ?string $modelLabel = 'Data Absensi';
    protected static ?string $pluralModelLabel = 'Data Absensi';
    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && ! $user->hasAnyRole(['super_admin', 'admin', 'pimpinan'])) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return AbsensiHarianForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AbsensiHarianInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AbsensiHariansTable::configure($table);
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
            'index' => ListAbsensiHarians::route('/'),
            'create' => CreateAbsensiHarian::route('/create'),
            'view' => ViewAbsensiHarian::route('/{record}'),
            'edit' => EditAbsensiHarian::route('/{record}/edit'),
        ];
    }
}
