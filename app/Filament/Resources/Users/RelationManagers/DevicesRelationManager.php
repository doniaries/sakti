<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class DevicesRelationManager extends RelationManager
{
    protected static string $relationship = 'devices';

    protected static ?string $title = 'Perangkat Terdaftar (HP / PWA / PC)';

    public function isReadOnly(): bool
    {
        /** @var User|null $authUser */
        $authUser = Auth::user();

        return ! ($authUser?->hasAnyRole(['super_admin', 'admin_kepegawaian', 'admin']) ?? false);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('device_name')
            ->defaultSort('last_login_at', 'desc')
            ->columns([
                TextColumn::make('device_name')
                    ->label('Nama Perangkat')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('platform')
                    ->label('Platform')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'android' => 'success',
                        'pwa' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'android' => 'Android APK',
                        'pwa' => 'Browser / PWA',
                        default => strtoupper($state),
                    }),

                TextColumn::make('device_id')
                    ->label('ID Perangkat')
                    ->fontFamily('mono')
                    ->copyable()
                    ->limit(24)
                    ->searchable(),

                TextColumn::make('ip_address')
                    ->label('IP Terakhir')
                    ->placeholder('-'),

                TextColumn::make('last_login_at')
                    ->label('Login Terakhir')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->headerActions([
                Action::make('reset_all_devices')
                    ->label('Reset Semua Perangkat')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Semua Perangkat Pegawai?')
                    ->modalDescription('Seluruh perangkat terdaftar dan sesi aplikasi mobile pegawai ini akan dihapus sehingga pegawai dapat login ulang di perangkat baru.')
                    ->action(function (): void {
                        /** @var User $owner */
                        $owner = $this->getOwnerRecord();
                        $owner->resetDeviceBinding();

                        Notification::make()
                            ->title('Perangkat pegawai berhasil direset.')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Hapus'),
            ]);
    }
}
