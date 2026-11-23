<?php

namespace App\Filament\Resources\AgendaKegiatans;

use App\Filament\Resources\AgendaKegiatans\Pages\CreateAgendaKegiatan;
use App\Filament\Resources\AgendaKegiatans\Pages\EditAgendaKegiatan;
use App\Filament\Resources\AgendaKegiatans\Pages\ListAgendaKegiatans;
use App\Filament\Resources\AgendaKegiatans\Schemas\AgendaKegiatanForm;
use App\Filament\Resources\AgendaKegiatans\Tables\AgendaKegiatansTable;
use App\Models\AgendaKegiatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AgendaKegiatanResource extends Resource
{
    protected static ?string $model = AgendaKegiatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Agenda Kegiatan';

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'admin']) || $user->hasPermissionTo('kelola_agenda');
    }

    public static function form(Schema $schema): Schema
    {
        return AgendaKegiatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgendaKegiatansTable::configure($table);
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
            'index' => ListAgendaKegiatans::route('/'),
            'create' => CreateAgendaKegiatan::route('/create'),
            'edit' => EditAgendaKegiatan::route('/{record}/edit'),
        ];
    }
}
