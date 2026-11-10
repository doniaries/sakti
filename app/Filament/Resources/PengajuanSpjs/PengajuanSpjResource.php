<?php

namespace App\Filament\Resources\PengajuanSpjs;

use App\Filament\Resources\PengajuanSpjs\Pages\CreatePengajuanSpj;
use App\Filament\Resources\PengajuanSpjs\Pages\EditPengajuanSpj;
use App\Filament\Resources\PengajuanSpjs\Pages\ListPengajuanSpjs;
use App\Filament\Resources\PengajuanSpjs\Schemas\PengajuanSpjForm;
use App\Filament\Resources\PengajuanSpjs\Schemas\PengajuanSpjInfolist;
use App\Filament\Resources\PengajuanSpjs\Tables\PengajuanSpjsTable;
use App\Filament\Resources\PengajuanSpjs\Widgets\KeuanganBidangOverviewWidget;
use App\Models\PengajuanSpj;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PengajuanSpjResource extends Resource
{
    protected static ?string $model = PengajuanSpj::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $modelLabel = 'Pengajuan SPJ';

    protected static ?string $pluralModelLabel = 'Pengajuan & SPJ Belanja';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return PengajuanSpjForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PengajuanSpjInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengajuanSpjsTable::configure($table);
    }

    public static function getWidgets(): array
    {
        return [
            KeuanganBidangOverviewWidget::class,
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && ! $user->canViewAllBidangKeuangan()) {
            $userBidangId = (int) ($user->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $user->id)->value('id') ?? 0));
            $query->where(function (\Illuminate\Database\Eloquent\Builder $q) use ($user, $userBidangId) {
                if ($userBidangId > 0) {
                    $q->where('bidang_id', $userBidangId);
                }
                $q->orWhere('user_id', $user->id);
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengajuanSpjs::route('/'),
            'create' => CreatePengajuanSpj::route('/create'),
            'edit' => EditPengajuanSpj::route('/{record}/edit'),
        ];
    }
}

