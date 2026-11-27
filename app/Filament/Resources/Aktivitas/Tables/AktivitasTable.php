<?php

namespace App\Filament\Resources\Aktivitas\Tables;

use App\Models\Aktivitas;
use App\Models\Bidang;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
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

class AktivitasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->with([
                'user:id,name',
                'jabatan:id,nama',
                'bidang:id,nama',
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
                    ->state(fn(Aktivitas $record): string => is_null($record->user_id) && is_null($record->jabatan_id) ? 'Global' : 'Spesifik')
                    ->color(fn(string $state): string => $state === 'Global' ? 'info' : 'warning'),
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
                TernaryFilter::make('kategori_global')
                    ->label('Kategori')
                    ->placeholder('Semua Aktivitas')
                    ->trueLabel('Global (Semua Pegawai)')
                    ->falseLabel('Spesifik (Per Pegawai / Jabatan)')
                    ->queries(
                        true: fn(Builder $q): Builder => $q->whereNull('user_id')->whereNull('jabatan_id'),
                        false: fn(Builder $q): Builder => $q->where(fn(Builder $sub) => $sub->whereNotNull('user_id')->orWhereNotNull('jabatan_id')),
                    ),
                SelectFilter::make('user_id')
                    ->label('Pegawai')
                    ->relationship('user', 'name')
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
                                            ->orderBy('name')
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
                                        ->orderBy('name')
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
                            $users = User::whereIn('id', $userIds)->get();
                            $copiedCount = 0;

                            foreach ($users as $pegawai) {
                                $nextKode = (int) Aktivitas::where('user_id', $pegawai->id)->count();
                                foreach ($records as $record) {
                                    $exists = Aktivitas::where('user_id', $pegawai->id)
                                        ->where('nama_aktivitas', $record->nama_aktivitas)
                                        ->exists();

                                    if (! $exists) {
                                        $nextKode++;
                                        Aktivitas::create([
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

                    BulkAction::make('jadikanGlobal')
                        ->label('Jadikan Global (Semua Pegawai)')
                        ->icon('heroicon-o-globe-alt')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Jadikan Aktivitas Global')
                        ->modalDescription('Aktivitas yang dipilih akan dijadikan data Global (seperti Apel Pagi) sehingga dapat dipakai oleh seluruh pegawai.')
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'user_id' => null,
                                    'jabatan_id' => null,
                                    'bidang_id' => null,
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
