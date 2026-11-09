<?php

namespace App\Filament\Resources\RincianAnggaranBelanjas;

use App\Filament\Resources\RincianAnggaranBelanjas\Pages\CreateRincianAnggaranBelanja;
use App\Filament\Resources\RincianAnggaranBelanjas\Pages\EditRincianAnggaranBelanja;
use App\Filament\Resources\RincianAnggaranBelanjas\Pages\ListRincianAnggaranBelanjas;
use App\Filament\Resources\RincianAnggaranBelanjas\Schemas\RincianAnggaranBelanjaForm;
use App\Filament\Resources\RincianAnggaranBelanjas\Tables\RincianAnggaranBelanjasTable;
use App\Models\RincianAnggaranBelanja;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RincianAnggaranBelanjaResource extends Resource
{
    protected static ?string $model = RincianAnggaranBelanja::class;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Rincian Belanja (RKA)';

    protected static ?string $modelLabel = 'Rincian Belanja RKA';

    protected static ?string $pluralModelLabel = 'Rincian Belanja (RKA)';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return RincianAnggaranBelanjaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RincianAnggaranBelanjasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && ! $user->canViewAllBidangKeuangan()) {
            $userBidangId = (int) ($user->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $user->id)->value('id') ?? 0));
            $query->where('bidang_id', $userBidangId);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRincianAnggaranBelanjas::route('/'),
            'create' => CreateRincianAnggaranBelanja::route('/create'),
            'edit' => EditRincianAnggaranBelanja::route('/{record}/edit'),
        ];
    }
}
