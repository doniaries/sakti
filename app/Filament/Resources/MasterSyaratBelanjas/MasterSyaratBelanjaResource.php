<?php

namespace App\Filament\Resources\MasterSyaratBelanjas;

use App\Filament\Resources\MasterSyaratBelanjas\Pages\CreateMasterSyaratBelanja;
use App\Filament\Resources\MasterSyaratBelanjas\Pages\EditMasterSyaratBelanja;
use App\Filament\Resources\MasterSyaratBelanjas\Pages\ListMasterSyaratBelanjas;
use App\Filament\Resources\MasterSyaratBelanjas\Schemas\MasterSyaratBelanjaForm;
use App\Filament\Resources\MasterSyaratBelanjas\Tables\MasterSyaratBelanjasTable;
use App\Models\MasterSyaratBelanja;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MasterSyaratBelanjaResource extends Resource
{
    protected static ?string $model = MasterSyaratBelanja::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $modelLabel = 'Syarat Dokumen SPJ';

    protected static ?string $pluralModelLabel = 'Syarat Dokumen SPJ';

    protected static ?string $slug = 'master-syarat-belanja-legacy';

    protected static ?string $cluster = \App\Filament\Clusters\MasterKeuangan::class;

    protected static ?int $navigationSort = 7;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        return (bool) $user?->hasAnyRole(['super_admin', 'admin']);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) MasterSyaratBelanja::where('aktif', true)->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Jumlah syarat dokumen SPJ aktif';
    }

    public static function form(Schema $schema): Schema
    {
        return MasterSyaratBelanjaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterSyaratBelanjasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterSyaratBelanjas::route('/'),
            'create' => CreateMasterSyaratBelanja::route('/create'),
            'edit' => EditMasterSyaratBelanja::route('/{record}/edit'),
        ];
    }
}
