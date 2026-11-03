<?php

namespace App\Filament\Resources\PengajuanSpjs;

use App\Filament\Resources\PengajuanSpjs\Pages\CreatePengajuanSpj;
use App\Filament\Resources\PengajuanSpjs\Pages\EditPengajuanSpj;
use App\Filament\Resources\PengajuanSpjs\Pages\ListPengajuanSpjs;
use App\Filament\Resources\PengajuanSpjs\Pages\ViewPengajuanSpj;
use App\Filament\Resources\PengajuanSpjs\Schemas\PengajuanSpjForm;
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

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan & SPJ';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return PengajuanSpjForm::configure($schema);
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

    public static function getPages(): array
    {
        return [
            'index' => ListPengajuanSpjs::route('/'),
            'create' => CreatePengajuanSpj::route('/create'),
            'view' => ViewPengajuanSpj::route('/{record}'),
            'edit' => EditPengajuanSpj::route('/{record}/edit'),
        ];
    }
}

