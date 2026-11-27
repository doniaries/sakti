<?php

namespace App\Filament\Resources\RencanaAksis\Tables;

use App\Models\RencanaAksi;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RencanaAksisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'jabatan:id,nama',
                'bidang:id,nama',
            ]))
            ->columns([
                TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'kabid' => 'Kabid',
                        'jf' => 'JF',
                        'staf' => 'Staf',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'kabid' => 'primary',
                        'jf' => 'info',
                        'staf' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('sasaran_sub_kegiatan')
                    ->label('Sub Kegiatan')
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rencana_aksi')
                    ->label('Rencana Aksi')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'trajectory' ? 'Trajectory' : 'Non-Trajectory')
                    ->color(fn (?string $state): string => $state === 'trajectory' ? 'warning' : 'gray'),
                TextColumn::make('satuan')
                    ->label('Satuan')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('jabatan.nama')
                    ->label('Jabatan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->badge()
                    ->color(fn (?string $state): string => \App\Models\Bidang::badgeColor($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tahun')
                    ->toggleable(isToggledHiddenByDefault: true),
                ...self::targetColumns(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('level')
                    ->label('Level Jabatan')
                    ->options([
                        'kabid' => 'Kabid',
                        'jf' => 'Jabatan Fungsional (JF)',
                        'staf' => 'Staf / Pelaksana',
                    ]),
                SelectFilter::make('sasaran_sub_kegiatan')
                    ->label('Sub Kegiatan')
                    ->options(fn (): array => \App\Models\RencanaAksi::query()
                        ->whereNotNull('sasaran_sub_kegiatan')
                        ->distinct()
                        ->orderBy('sasaran_sub_kegiatan')
                        ->pluck('sasaran_sub_kegiatan', 'sasaran_sub_kegiatan')
                        ->all())
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Kolom target bulanan dengan modal edit cepat saat sel target diklik.
     *
     * @return array<int, TextColumn>
     */
    protected static function targetColumns(): array
    {
        $months = [
            'target_jan' => 'Jan',
            'target_feb' => 'Feb',
            'target_mar' => 'Mar',
            'target_apr' => 'Apr',
            'target_mei' => 'Mei',
            'target_jun' => 'Jun',
            'target_jul' => 'Jul',
            'target_agu' => 'Agu',
            'target_sep' => 'Sep',
            'target_okt' => 'Okt',
            'target_nov' => 'Nov',
            'target_des' => 'Des',
        ];

        $columns = [];

        foreach ($months as $column => $label) {
            $columns[] = TextColumn::make($column)
                ->label('Target ' . $label)
                ->numeric()
                ->sortable()
                ->tooltip('Klik untuk edit target')
                ->action(
                    Action::make('edit_target_' . $column)
                        ->modalHeading('Edit Target Bulanan')
                        ->modalDescription(fn (RencanaAksi $record): string => $record->rencana_aksi ?? '')
                        ->modalSubmitActionLabel('Simpan Target')
                        ->modalWidth('2xl')
                        ->fillForm(fn (RencanaAksi $record): array => $record->only(array_keys($months)))
                        ->schema([
                            Grid::make(3)
                                ->schema(
                                    collect($months)
                                        ->map(fn (string $mLabel, string $mKey): TextInput => TextInput::make($mKey)
                                            ->label('Target ' . $mLabel)
                                            ->numeric()
                                            ->minValue(0)
                                            ->required()
                                            ->default(0)
                                            ->suffix(fn (RencanaAksi $record): ?string => $record->satuan ?: null)
                                            ->autofocus($mKey === $column))
                                        ->values()
                                        ->all()
                                ),
                        ])
                        ->action(function (array $data, RencanaAksi $record): void {
                            $record->update($data);

                            Notification::make()
                                ->title('Target bulanan berhasil diperbarui')
                                ->success()
                                ->send();
                        })
                );
        }

        return $columns;
    }
}
