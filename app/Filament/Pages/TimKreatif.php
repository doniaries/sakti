<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\JabatanTimKreatif;
use App\Filament\Resources\Users\UserResource;
use App\Models\Bidang;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class TimKreatif extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|UnitEnum|null $navigationGroup = 'Tim Kreatif';

    protected static ?string $navigationLabel = 'Tim Kreatif';

    protected static ?string $title = 'Tim Kreatif Dinas';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.tim-kreatif';

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user !== null && ($user->hasRole('super_admin') || filled($user->jabatan_tim_kreatif));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereNotNull('jabatan_tim_kreatif')
                    ->where('aktif', true)
                    ->bukanSuperAdmin()
                    ->with(['bidang:id,nama', 'jabatan:id,nama'])
            )
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('jabatan_tim_kreatif')
                    ->label('Jabatan Tim Kreatif')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->badge()
                    ->color(fn (?string $state): string => Bidang::badgeColor($state))
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jabatan.nama')
                    ->label('Jabatan Dinas')
                    ->placeholder('-')
                    ->searchable()
                    ->limit(35),

                TextColumn::make('no_hp')
                    ->label('No. HP / WA')
                    ->placeholder('-')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('jabatan_tim_kreatif')
                    ->label('Jabatan Tim Kreatif')
                    ->options(JabatanTimKreatif::class),

                SelectFilter::make('bidang_id')
                    ->label('Bidang')
                    ->options(fn (): array => Bidang::query()->orderBy('nama')->pluck('nama', 'id')->all()),
            ])
            ->headerActions([
                Action::make('kelola_anggota')
                    ->label('Kelola di Data Pegawai')
                    ->icon('heroicon-o-users')
                    ->color('gray')
                    ->visible(function (): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return $user !== null && ($user->hasRole('super_admin') || $user->can('Update:User'));
                    })
                    ->url(fn (): string => UserResource::getUrl('index', ['activeTab' => 'tim_kreatif'])),
            ])
            ->recordActions([
                Action::make('ubah_jabatan_tim')
                    ->label('Ubah Jabatan')
                    ->icon('heroicon-o-pencil-square')
                    ->visible(function (): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return $user !== null && ($user->hasRole('super_admin') || $user->can('Update:User'));
                    })
                    ->fillForm(fn (User $record): array => [
                        'jabatan_tim_kreatif' => $record->jabatan_tim_kreatif?->value,
                    ])
                    ->schema([
                        Select::make('jabatan_tim_kreatif')
                            ->label('Jabatan Tim Kreatif Dinas')
                            ->options(JabatanTimKreatif::class)
                            ->placeholder('Keluarkan dari Tim Kreatif')
                            ->searchable(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update([
                            'jabatan_tim_kreatif' => $data['jabatan_tim_kreatif'] ?: null,
                        ]);

                        Notification::make()
                            ->title('Jabatan Tim Kreatif diperbarui')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
