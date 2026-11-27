<?php

namespace App\Filament\Resources\Aktivitas\Tables;

use App\Models\Aktivitas;
use App\Models\Bidang;
use App\Models\RencanaAksi;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class AktivitasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->with([
                'user:id,name',
                'jabatan:id,nama',
                'bidang:id,nama',
                'rencanaAksi:id,sasaran_sub_kegiatan',
            ]))
            ->columns([
                TextColumn::make('rencanaAksi.sasaran_sub_kegiatan')
                    ->label('Sub Kegiatan')
                    ->placeholder('Umum / Global')
                    ->wrap()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('nama_aktivitas')
                    ->label('Nama Aktivitas')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('menit')
                    ->label('Durasi')
                    ->badge()
                    ->numeric()
                    ->color(fn ($state): string => match (true) {
                        (int) $state >= 240 => 'danger',
                        (int) $state >= 120 => 'warning',
                        (int) $state >= 60 => 'info',
                        default => 'success',
                    })
                    ->formatStateUsing(function ($state): string {
                        $menit = (int) $state;
                        if ($menit >= 60) {
                            $jam = intdiv($menit, 60);
                            $sisa = $menit % 60;
                            return $sisa > 0 ? "{$menit} mnt ({$jam}j {$sisa}m)" : "{$menit} mnt ({$jam} jam)";
                        }
                        return "{$menit} mnt";
                    })
                    ->sortable(),
                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->state(function (Aktivitas $record): string {
                        if (is_null($record->user_id) && is_null($record->jabatan_id)) {
                            return 'Global';
                        }
                        if ($record->rencanaAksi?->sasaran_sub_kegiatan === 'Tugas Tambahan') {
                            return 'Tim Kreatif';
                        }

                        return $record->bidang?->nama ?? 'Per Pegawai / Jabatan';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Global' => 'info',
                        'Tim Kreatif' => 'warning',
                        'Per Pegawai / Jabatan' => 'gray',
                        default => Bidang::badgeColor($state),
                    }),
                TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->placeholder('Umum / Global')
                    ->badge()
                    ->color(fn(?string $state): string => $state ? 'info' : 'gray')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('jabatan.nama')
                    ->label('Jabatan')
                    ->placeholder('-')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->placeholder('-')
                    ->badge()
                    ->color(fn(?string $state): string => $state ? \App\Models\Bidang::badgeColor($state) : 'gray')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kategori_aktivitas')
                    ->label('Kategori')
                    ->placeholder('Semua Aktivitas')
                    ->options([
                        'global' => 'Global (Semua Pegawai)',
                        'tim_kreatif' => 'Tim Kreatif',
                        'bidang' => 'Per Bidang / Pegawai',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'global' => $query->whereNull('user_id')->whereNull('jabatan_id'),
                        'tim_kreatif' => $query->whereHas('rencanaAksi', fn (Builder $ra) => $ra->where('sasaran_sub_kegiatan', 'Tugas Tambahan')),
                        'bidang' => $query->where(fn (Builder $sub) => $sub->whereNotNull('user_id')->orWhereNotNull('jabatan_id'))
                            ->whereDoesntHave('rencanaAksi', fn (Builder $ra) => $ra->where('sasaran_sub_kegiatan', 'Tugas Tambahan')),
                        default => $query,
                    }),
                SelectFilter::make('user_id')
                    ->label('Pegawai')
                    ->relationship('user', 'name', fn (Builder $query): Builder => $query->orderBy('id'))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('bidang_id')
                    ->label('Bidang')
                    ->relationship('bidang', 'nama')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('level_rencana_aksi')
                    ->label('Level Jabatan (Rencana Aksi)')
                    ->options([
                        'kabid' => 'Kabid',
                        'jf' => 'Jabatan Fungsional (JF)',
                        'staf' => 'Staf / Pelaksana',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('rencanaAksi', fn (Builder $ra) => $ra->where('level', $data['value']))
                        : $query),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('salinKePegawai')
                        ->label('Salin ke Pegawai (Per Bidang)')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->modalHeading('Salin Aktivitas ke Pegawai')
                        ->modalDescription('Pilih Bidang untuk otomatis mencentang seluruh pegawai di bidang tersebut, atau pilih pegawai secara manual.')
                        ->modalWidth('2xl')
                        ->form([
                            Select::make('bidang_id')
                                ->label('Filter Bidang (Otomatis Pilih Pegawai Bidang)')
                                ->options(fn() => Bidang::orderBy('nama')->pluck('nama', 'id')->all())
                                ->placeholder('Semua Bidang')
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set): void {
                                    if ($state) {
                                        $ids = User::where('aktif', true)
                                            ->where('bidang_id', (int) $state)
                                            ->orderBy('id')
                                            ->pluck('id')
                                            ->map(fn($id) => (string) $id)
                                            ->all();
                                        $set('user_ids', $ids);
                                    } else {
                                        $set('user_ids', []);
                                    }
                                }),

                            CheckboxList::make('user_ids')
                                ->label('Pilih Pegawai Tujuan')
                                ->options(function (Get $get): array {
                                    $bidangId = $get('bidang_id');

                                    return User::with(['bidang', 'jabatan'])
                                        ->where('aktif', true)
                                        ->when($bidangId, fn($q) => $q->where('bidang_id', (int) $bidangId))
                                        ->orderBy('id')
                                        ->get()
                                        ->mapWithKeys(function (User $u): array {
                                            $info = collect([$u->bidang?->nama, $u->jabatan?->nama])
                                                ->filter()
                                                ->implode(' • ');

                                            return [(string) $u->id => $info ? "{$u->name} ({$info})" : $u->name];
                                        })
                                        ->all();
                                })
                                ->bulkToggleable()
                                ->searchable()
                                ->columns(2)
                                ->required()
                                ->helperText('Setiap aktivitas yang disalin otomatis dikaitkan dengan pegawai, jabatan, dan bidang masing-masing.'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $userIds = $data['user_ids'] ?? [];
                            $users = User::query()->findMany($userIds);
                            $copiedCount = 0;

                            foreach ($users as $pegawai) {
                                $nextKode = (int) Aktivitas::query()->where('user_id', $pegawai->id)->count();
                                foreach ($records as $record) {
                                    $exists = Aktivitas::query()
                                        ->where('user_id', $pegawai->id)
                                        ->where('nama_aktivitas', $record->nama_aktivitas)
                                        ->exists();

                                    if (! $exists) {
                                        $nextKode++;
                                        Aktivitas::query()->create([
                                            'user_id' => $pegawai->id,
                                            'jabatan_id' => $pegawai->jabatan_id,
                                            'bidang_id' => $pegawai->bidang_id,
                                            'kode' => $record->kode ?: (string) $nextKode,
                                            'nama_aktivitas' => $record->nama_aktivitas,
                                            'menit' => $record->menit,
                                        ]);
                                        $copiedCount++;
                                    }
                                }
                            }

                            Notification::make()
                                ->title('Berhasil Disalin')
                                ->body("{$copiedCount} item aktivitas berhasil disalin ke " . $users->count() . ' pegawai.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('pindahKategori')
                        ->label('Pindah Kategori / Rencana Aksi')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('warning')
                        ->modalHeading('Pindah Kategori Bank Aktivitas')
                        ->modalDescription('Pindahkan aktivitas yang dipilih ke kategori Umum/Global, Bidang/Pegawai, atau Tim Kreatif.')
                        ->modalWidth('xl')
                        ->form([
                            Radio::make('target_kategori')
                                ->label('Kategori Tujuan')
                                ->options(function (): array {
                                    /** @var \App\Models\User|null $user */
                                    $user = Auth::user();
                                    $options = [];

                                    if ($user && $user->hasAnyRole(['super_admin', 'superadmin'])) {
                                        $options['global'] = 'Umum / Global (Semua Pegawai)';
                                    }

                                    $options['bidang'] = 'Per Bidang / Pegawai';
                                    $options['tim_kreatif'] = 'Tim Kreatif (Tugas Tambahan)';

                                    return $options;
                                })
                                ->default('bidang')
                                ->inline()
                                ->live()
                                ->required(),

                            Select::make('bidang_id')
                                ->label('Bidang Tujuan (Opsional)')
                                ->options(fn () => Bidang::orderBy('nama')->pluck('nama', 'id')->all())
                                ->placeholder('Pertahankan bidang saat ini')
                                ->searchable()
                                ->live()
                                ->visible(fn (Get $get): bool => $get('target_kategori') === 'bidang'),

                            Select::make('user_id')
                                ->label('Pegawai Pemilik (Opsional)')
                                ->options(function (Get $get): array {
                                    $kategori = $get('target_kategori');
                                    $bidangId = $get('bidang_id');

                                    return User::query()
                                        ->where('aktif', true)
                                        ->when($kategori === 'tim_kreatif', fn ($q) => $q->whereNotNull('jabatan_tim_kreatif'))
                                        ->when($kategori === 'bidang' && $bidangId, fn ($q) => $q->where('bidang_id', (int) $bidangId))
                                        ->orderBy('id')
                                        ->pluck('name', 'id')
                                        ->all();
                                })
                                ->placeholder('Pertahankan pegawai saat ini')
                                ->searchable()
                                ->nullable()
                                ->helperText('Kosongkan jika hanya ingin mengubah bidang atau Rencana Aksi tanpa mengganti pegawai.')
                                ->visible(fn (Get $get): bool => in_array($get('target_kategori'), ['bidang', 'tim_kreatif'], true)),

                            Select::make('rencana_aksi_id')
                                ->label('Rencana Aksi / Sub Kegiatan')
                                ->options(function (Get $get): array {
                                    $kategori = $get('target_kategori');
                                    $bidangId = $get('bidang_id');

                                    return RencanaAksi::query()
                                        ->when(
                                            $kategori === 'tim_kreatif',
                                            fn ($q) => $q->where('sasaran_sub_kegiatan', 'Tugas Tambahan'),
                                            fn ($q) => $q->where('sasaran_sub_kegiatan', '!=', 'Tugas Tambahan')
                                                ->when($bidangId, fn ($b) => $b->where('bidang_id', (int) $bidangId))
                                        )
                                        ->orderBy('sasaran_sub_kegiatan')
                                        ->orderBy('rencana_aksi')
                                        ->get(['id', 'sasaran_sub_kegiatan', 'rencana_aksi', 'level'])
                                        ->mapWithKeys(fn (RencanaAksi $ra): array => [
                                            $ra->id => '[' . strtoupper((string) ($ra->level ?? '-')) . '] ' . ($ra->sasaran_sub_kegiatan ?: 'Umum') . ' — ' . $ra->rencana_aksi,
                                        ])
                                        ->all();
                                })
                                ->placeholder('Pilih Rencana Aksi tujuan (opsional)')
                                ->searchable()
                                ->nullable()
                                ->visible(fn (Get $get): bool => in_array($get('target_kategori'), ['bidang', 'tim_kreatif'], true)),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            /** @var \App\Models\User|null $user */
                            $user = Auth::user();
                            $isSuperAdmin = $user && $user->hasAnyRole(['super_admin', 'superadmin']);

                            $kategori = $data['target_kategori'] ?? 'bidang';
                            if ($kategori === 'global' && ! $isSuperAdmin) {
                                Notification::make()
                                    ->title('Akses Ditolak')
                                    ->body('Kategori Umum / Global hanya dapat diatur oleh Super Admin.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $pegawai = ! empty($data['user_id']) ? User::find((int) $data['user_id']) : null;
                            $bidangId = ! empty($data['bidang_id']) ? (int) $data['bidang_id'] : null;
                            $rencanaAksiId = ! empty($data['rencana_aksi_id']) ? (int) $data['rencana_aksi_id'] : null;

                            foreach ($records as $record) {
                                if ($kategori === 'global') {
                                    $record->update([
                                        'user_id' => null,
                                        'jabatan_id' => null,
                                        'bidang_id' => null,
                                        'rencana_aksi_id' => null,
                                    ]);
                                    continue;
                                }

                                $updates = [];
                                if ($pegawai) {
                                    $updates['user_id'] = $pegawai->id;
                                    $updates['jabatan_id'] = $pegawai->jabatan_id;
                                    $updates['bidang_id'] = $pegawai->bidang_id;
                                } elseif ($bidangId) {
                                    $updates['bidang_id'] = $bidangId;
                                }

                                if ($rencanaAksiId) {
                                    $updates['rencana_aksi_id'] = $rencanaAksiId;
                                }

                                if ($updates !== []) {
                                    $record->update($updates);
                                }
                            }

                            Notification::make()
                                ->title('Kategori Aktivitas Diperbarui')
                                ->body($records->count() . ' item Bank Aktivitas berhasil dipindahkan.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('jadikanGlobal')
                        ->label('Jadikan Global (Semua Pegawai)')
                        ->icon('heroicon-o-globe-alt')
                        ->color('success')
                        ->visible(function (): bool {
                            /** @var \App\Models\User|null $user */
                            $user = Auth::user();

                            return $user?->hasAnyRole(['super_admin', 'superadmin']) ?? false;
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Jadikan Aktivitas Global')
                        ->modalDescription('Aktivitas yang dipilih akan dijadikan data Global (seperti Apel Pagi) sehingga dapat dipakai oleh seluruh pegawai.')
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'user_id' => null,
                                    'jabatan_id' => null,
                                    'bidang_id' => null,
                                    'rencana_aksi_id' => null,
                                ]);
                            }

                            Notification::make()
                                ->title('Aktivitas Dijadikan Global')
                                ->body($records->count() . ' aktivitas kini dapat diakses oleh semua pegawai.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
