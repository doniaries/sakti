<?php

namespace App\Filament\Resources\PengajuanIzins;

use App\Filament\Resources\PengajuanIzins\Pages\CreatePengajuanIzin;
use App\Filament\Resources\PengajuanIzins\Pages\EditPengajuanIzin;
use App\Filament\Resources\PengajuanIzins\Pages\ListPengajuanIzins;
use App\Filament\Resources\PengajuanIzins\Schemas\PengajuanIzinForm;
use App\Filament\Resources\PengajuanIzins\Tables\PengajuanIzinsTable;
use App\Models\PengajuanIzin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PengajuanIzinResource extends Resource
{
    protected static ?string $model = PengajuanIzin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $modelLabel = 'Izin & Cuti';
    protected static ?string $pluralModelLabel = 'Pengajuan Izin & Cuti';

    protected static string|\UnitEnum|null $navigationGroup = 'Absensi';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Pegawai biasa hanya melihat pengajuan dirinya sendiri;
        // Kepala Bidang / PPTK melihat pengajuan dirinya + staf di bawah binaannya.
        if ($user && ! $user->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian', 'pa', 'pimpinan', 'ppk', 'sekretaris'])) {
            if ($user->hasAnyRole(['pptk', 'kabid', 'kepala_bidang'])) {
                $query->where(function (Builder $q) use ($user): void {
                    $q->where('user_id', $user->id)
                        ->orWhereHas('user', function (Builder $u) use ($user): void {
                            $u->where('atasan_id', $user->id);
                            if ($user->bidang_id) {
                                $u->orWhere('bidang_id', $user->bidang_id);
                            }
                        });
                });
            } else {
                $query->where('user_id', $user->id);
            }
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return PengajuanIzinForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengajuanIzinsTable::configure($table);
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
            'index' => ListPengajuanIzins::route('/'),
            'create' => CreatePengajuanIzin::route('/create'),
            'edit' => EditPengajuanIzin::route('/{record}/edit'),
        ];
    }
}
