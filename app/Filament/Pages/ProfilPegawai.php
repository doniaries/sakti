<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ProfilPegawai extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $navigationLabel = 'Profil Pegawai';

    protected static ?string $title = 'Profil Pegawai';

    protected static ?string $slug = 'profil-pegawai';

    public ?User $record = null;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return Auth::check();
    }

    public function mount(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user !== null, 403);

        $this->record = $user->load([
            'bidang',
            'jabatan',
            'atasan',
            'jfPenempatan',
            'roles',
            'jadwalKerja',
            'riwayatGolongan',
            'riwayatJabatan',
            'riwayatPendidikan',
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return UserInfolist::configure($schema->record($this->record));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('presensi_mobile')
                ->label('Buka Presensi Mobile')
                ->icon('heroicon-o-device-phone-mobile')
                ->color('gray')
                ->url(fn (): string => route('mobile.dashboard'), shouldOpenInNewTab: true),
        ];
    }
}

