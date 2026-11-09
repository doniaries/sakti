<?php

namespace App\Filament\Resources\MasterJenisBelanjas;

use App\Filament\Resources\MasterJenisBelanjas\Pages\CreateMasterJenisBelanja;
use App\Filament\Resources\MasterJenisBelanjas\Pages\EditMasterJenisBelanja;
use App\Filament\Resources\MasterJenisBelanjas\Pages\ListMasterJenisBelanjas;
use App\Filament\Resources\MasterJenisBelanjas\RelationManagers\SyaratDokumenRelationManager;
use App\Filament\Resources\MasterJenisBelanjas\Schemas\MasterJenisBelanjaForm;
use App\Filament\Resources\MasterJenisBelanjas\Tables\MasterJenisBelanjasTable;
use App\Models\MasterJenisBelanja;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MasterJenisBelanjaResource extends Resource
{
    protected static ?string $model = MasterJenisBelanja::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $modelLabel = 'Jenis Pekerjaan';

    protected static ?string $pluralModelLabel = 'Syarat Dokumen SPJ';

    protected static ?string $navigationLabel = 'Syarat Dokumen SPJ';

    protected static ?string $slug = 'master-syarat-belanjas';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Keuangan';

    protected static ?int $navigationSort = 6;

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->hasAnyRole(['super_admin', 'admin']);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) MasterJenisBelanja::where('aktif', true)->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Jumlah jenis pekerjaan aktif';
    }

    public static function form(Schema $schema): Schema
    {
        return MasterJenisBelanjaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterJenisBelanjasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SyaratDokumenRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterJenisBelanjas::route('/'),
            'create' => CreateMasterJenisBelanja::route('/create'),
            'edit' => EditMasterJenisBelanja::route('/{record}/edit'),
        ];
    }
}

