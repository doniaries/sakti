<?php

namespace App\Filament\Resources\Lhks;

use App\Filament\Resources\Lhks\Pages;
use App\Filament\Resources\Lhks\Schemas\LhkForm;
use App\Models\Lhk;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\Lhks\Tables\LhksTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LhkResource extends Resource
{
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'LHK';
    protected static ?string $model = Lhk::class;
    protected static ?string $modelLabel = 'Laporan LHK';
    protected static ?string $pluralModelLabel = 'Laporan LHK';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && ! $user->canViewAllBidangKepegawaian()) {
            if ($user->hasRole('pptk') || filled($user->bidang_id)) {
                $userBidangId = (int) ($user->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $user->id)->value('id') ?? 0));
                $query->whereHas('user', fn ($uq) => $uq->where('bidang_id', $userBidangId)->orWhere('atasan_id', $user->id));
            } else {
                $query->where('user_id', $user->id);
            }
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return LhkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LhksTable::configure($table);
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
            'index'  => Pages\ListLhks::route('/'),
            'create' => Pages\CreateLhk::route('/create'),
            'edit'   => Pages\EditLhk::route('/{record}/edit'),
        ];
    }
}
