<?php

namespace App\Filament\Resources\LogAbsensis;

use App\Filament\Resources\LogAbsensis\Pages\CreateLogAbsensi;
use App\Filament\Resources\LogAbsensis\Pages\EditLogAbsensi;
use App\Filament\Resources\LogAbsensis\Pages\ListLogAbsensis;
use App\Filament\Resources\LogAbsensis\Pages\ViewLogAbsensi;
use App\Filament\Resources\LogAbsensis\Schemas\LogAbsensiForm;
use App\Filament\Resources\LogAbsensis\Schemas\LogAbsensiInfolist;
use App\Filament\Resources\LogAbsensis\Tables\LogAbsensisTable;
use App\Models\LogAbsensi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LogAbsensiResource extends Resource
{
    protected static ?string $model = LogAbsensi::class;

    protected static ?string $navigationLabel = 'Log Web & Mobile';

    protected static string|\UnitEnum|null $navigationGroup = 'Absensi';
    protected static ?string $modelLabel = 'Log Absensi';
    protected static ?string $pluralModelLabel = 'Log Absensi';
    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'nomor_absensi';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && ! $user->hasAnyRole(['super_admin', 'admin', 'pimpinan', 'pa', 'pptk', 'ppk'])) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return LogAbsensiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LogAbsensiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LogAbsensisTable::configure($table);
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
            'index' => ListLogAbsensis::route('/'),
            // 'create' => CreateLogAbsensi::route('/create'),
            // 'view' => ViewLogAbsensi::route('/{record}'),
            // 'edit' => EditLogAbsensi::route('/{record}/edit'),
        ];
    }
}
