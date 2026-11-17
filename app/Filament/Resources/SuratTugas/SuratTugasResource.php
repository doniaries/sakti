<?php

declare(strict_types=1);

namespace App\Filament\Resources\SuratTugas;

use App\Filament\Resources\SuratTugas\Pages\CreateSuratTugas;
use App\Filament\Resources\SuratTugas\Pages\EditSuratTugas;
use App\Filament\Resources\SuratTugas\Pages\ListSuratTugas;
use App\Filament\Resources\SuratTugas\Schemas\SuratTugasForm;
use App\Filament\Resources\SuratTugas\Tables\SuratTugasTable;
use App\Models\SuratTugas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SuratTugasResource extends Resource
{
    protected static ?string $model = SuratTugas::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Tim Kreatif';

    protected static ?string $navigationLabel = 'Surat Tugas';

    protected static ?string $modelLabel = 'Surat Tugas';

    protected static ?string $pluralModelLabel = 'Surat Tugas';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return SuratTugasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuratTugasTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        $unread = SuratTugas::query()
            ->where('status', 'terbit')
            ->whereHas('pegawais', fn (Builder $q) => $q->where('users.id', $user->id)->whereNull('surat_tugas_user.dibaca_at'))
            ->count();

        return $unread > 0 ? (string) $unread : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function canViewAny(): bool
    {
        if (! \App\Models\PengaturanAplikasi::isModuleEnabled('surat_tugas')) {
            return false;
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user !== null && ($user->canManageSuratTugas() || filled($user->jabatan_tim_kreatif));
    }

    public static function canCreate(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->canManageSuratTugas() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->canManageSuratTugas() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->hasRole('super_admin') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->hasRole('super_admin') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $query = parent::getEloquentQuery()
            ->with(['penandatangan'])
            ->withCount(['pegawais', 'pegawaisSudahBaca']);

        if ($user && ! $user->canManageSuratTugas()) {
            $query->where(function (Builder $q) use ($user): void {
                $q->where('penandatangan_id', $user->id)
                    ->orWhereHas('pegawais', fn (Builder $sub) => $sub->where('users.id', $user->id));
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuratTugas::route('/'),
            'create' => CreateSuratTugas::route('/create'),
            'edit' => EditSuratTugas::route('/{record}/edit'),
        ];
    }
}
