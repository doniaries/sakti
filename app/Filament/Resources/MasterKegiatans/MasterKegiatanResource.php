<?php

namespace App\Filament\Resources\MasterKegiatans;

use App\Filament\Resources\MasterKegiatans\Pages\CreateMasterKegiatan;
use App\Filament\Resources\MasterKegiatans\Pages\EditMasterKegiatan;
use App\Filament\Resources\MasterKegiatans\Pages\ListMasterKegiatans;
use App\Filament\Resources\MasterKegiatans\Schemas\MasterKegiatanForm;
use App\Filament\Resources\MasterKegiatans\Tables\MasterKegiatansTable;
use App\Models\MasterKegiatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MasterKegiatanResource extends Resource
{
    protected static ?string $model = MasterKegiatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $modelLabel = 'Kegiatan';

    protected static ?string $pluralModelLabel = 'Kegiatan';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return MasterKegiatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterKegiatansTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && ! $user->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara', 'pimpinan'])) {
            if ($user->hasRole('jf') && $user->bidang_id) {
                $query->where('bidang_id', $user->bidang_id);
            }
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterKegiatans::route('/'),
            'create' => CreateMasterKegiatan::route('/create'),
            'edit' => EditMasterKegiatan::route('/{record}/edit'),
        ];
    }
}
