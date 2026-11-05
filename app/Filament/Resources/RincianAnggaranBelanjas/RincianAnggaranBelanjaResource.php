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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Master Keuangan';

    protected static ?string $navigationLabel = 'Rincian Belanja (RKA)';

    protected static ?string $modelLabel = 'Rincian Belanja RKA';

    protected static ?string $pluralModelLabel = 'Rincian Belanja (RKA)';

    protected static ?int $navigationSort = 6;

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
        if ($user && $user->hasRole('jf') && ! $user->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara'])) {
            $query->where(function ($q) use ($user) {
                if ($user->bidang_id) {
                    $q->where('bidang_id', $user->bidang_id)
                        ->orWhereHas('subKegiatan.kegiatan', fn ($kq) => $kq->where('bidang_id', $user->bidang_id));
                }
                $q->orWhereHas('subKegiatan', fn ($sq) => $sq->where('jf_id', $user->id));
            });
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
