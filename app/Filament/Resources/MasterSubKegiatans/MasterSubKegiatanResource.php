<?php

namespace App\Filament\Resources\MasterSubKegiatans;

use App\Filament\Resources\MasterSubKegiatans\Pages\CreateMasterSubKegiatan;
use App\Filament\Resources\MasterSubKegiatans\Pages\EditMasterSubKegiatan;
use App\Filament\Resources\MasterSubKegiatans\Pages\ListMasterSubKegiatans;
use App\Filament\Resources\MasterSubKegiatans\Schemas\MasterSubKegiatanForm;
use App\Filament\Resources\MasterSubKegiatans\Tables\MasterSubKegiatansTable;
use App\Models\MasterSubKegiatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MasterSubKegiatanResource extends Resource
{
    protected static ?string $model = MasterSubKegiatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $modelLabel = 'Sub Kegiatan';

    protected static ?string $pluralModelLabel = 'Sub Kegiatan';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return MasterSubKegiatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterSubKegiatansTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && ! $user->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara', 'pimpinan'])) {
            if ($user->hasRole('jf')) {
                $query->where(function ($q) use ($user) {
                    if ($user->bidang_id) {
                        $q->whereHas('kegiatan', fn ($kq) => $kq->where('bidang_id', $user->bidang_id));
                    }
                    $q->orWhere('jf_id', $user->id);
                });
            }
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterSubKegiatans::route('/'),
            'create' => CreateMasterSubKegiatan::route('/create'),
            'edit' => EditMasterSubKegiatan::route('/{record}/edit'),
        ];
    }
}
