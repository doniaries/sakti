<?php

namespace App\Filament\Resources\MasterPrograms\Tables;

use App\Models\MasterProgram;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $query->withCount('kegiatans')
                    ->where('tahun_anggaran', \App\Models\PengaturanAplikasi::getTahunAnggaranAktif());

                return $query;
            })
            ->columns([
                TextColumn::make('tahun_anggaran')
                    ->label('Tahun')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('kode_program')
                    ->label('Kode Program')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama_program')
                    ->label('Nama Program')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->formatStateUsing(fn (?string $state): string => mb_strtoupper((string) $state, 'UTF-8')),

                TextColumn::make('kegiatans_count')
                    ->label('Jml Kegiatan')
                    ->counts('kegiatans')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->tooltip('Klik untuk melihat rincian Kegiatan & Sub Kegiatan')
                    ->action(
                        Action::make('lihat_kegiatan_modal')
                            ->modalHeading(fn (MasterProgram $record): string => "Pohon Program: [{$record->kode_program}] {$record->nama_program}")
                            ->modalDescription('Rincian Kegiatan dan Sub Kegiatan yang berada di bawah program ini.')
                            ->modalWidth(MaxWidth::FiveExtraLarge)
                            ->modalContent(fn (MasterProgram $record) => view('filament.modals.program-kegiatan-tree', ['program' => $record]))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Tutup')
                    ),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\Action::make('select_tahun_anggaran')
                    ->view('filament.tables.toolbar.tahun-anggaran-select'),

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

