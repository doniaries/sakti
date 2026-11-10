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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?string $navigationLabel = 'Sub Kegiatan';

    protected static ?string $modelLabel = 'Sub Kegiatan';

    protected static ?string $pluralModelLabel = 'Sub Kegiatan';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 2;

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
        if (! $user) {
            return $query;
        }

        // 1. Role Pemeriksa Global & Super Admin / Admin / PA / PPK / Bendahara
        if ($user->canViewAllBidangKeuangan() || $user->hasRole('admin')) {
            return $query;
        }

        // 2. Role JF (Pejabat Fungsional / Pengampu):
        // HANYA melihat Sub Kegiatan yang ditugaskan kepada dirinya sendiri (jf_id == user->id).
        // RKA bidang yang sama milik rekan lain maupun bidang lain TIDAK TAMPAK.
        if ($user->hasRole('jf') && ! $user->hasRole('pptk')) {
            return $query->where('jf_id', $user->id);
        }

        // 3. Role PPTK (Kepala Bidang):
        // Melihat SELURUH Sub Kegiatan / RKA di bidangnya.
        $userBidangId = (int) ($user->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $user->id)->value('id') ?? 0));
        if ($userBidangId > 0) {
            return $query->whereHas('kegiatan', fn ($kq) => $kq->where('bidang_id', $userBidangId));
        }

        // Fallback untuk pegawai biasa: hanya yang ditugaskan kepadanya
        return $query->where('jf_id', $user->id);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RincianAnggaranBelanjaRelationManager::class,
        ];
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
