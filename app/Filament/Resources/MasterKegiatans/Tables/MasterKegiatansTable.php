<?php

namespace App\Filament\Resources\MasterKegiatans\Tables;

use App\Models\Bidang;
use App\Models\MasterKegiatan;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterKegiatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $query->with(['bidang', 'program'])->withCount('subKegiatans')
                    ->where('tahun_anggaran', \App\Models\PengaturanAplikasi::getTahunAnggaranAktif());

                return $query;
            })
            ->columns([
                TextColumn::make('tahun_anggaran')
                    ->label('Tahun')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('bidang.nama')
                    ->label('Bidang ')
                    ->badge()
                    ->color(fn(?string $state): string => Bidang::badgeColor($state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('program.nama_program')
                    ->label('Program Induk')
                    ->description(fn($record) => $record->program?->kode_program)
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->formatStateUsing(fn(?string $state): string => mb_strtoupper((string) $state, 'UTF-8')),

                TextColumn::make('kode_kegiatan')
                    ->label('Kode Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama_kegiatan')
                    ->label('Nama Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->formatStateUsing(fn(?string $state): string => mb_strtoupper((string) $state, 'UTF-8')),

                TextColumn::make('pagu_anggaran')
                    ->label('Jumlah Uang Kegiatan')
                    ->formatStateUsing(fn ($state) => $state !== null ? 'Rp ' . number_format((float) $state, 0, ',', '.') : '-')
                    ->sortable(),

                TextColumn::make('sub_kegiatans_count')
                    ->label('Jml Sub Kegiatan')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->tooltip('Klik untuk melihat rincian Sub Kegiatan')
                    ->action(
                        Action::make('lihat_sub_kegiatan_modal')
                            ->modalHeading(fn (MasterKegiatan $record): string => "Kegiatan: [{$record->kode_kegiatan}] {$record->nama_kegiatan}")
                            ->modalDescription('Rincian Sub Kegiatan yang berada di bawah kegiatan ini.')
                            ->modalWidth(Width::FourExtraLarge)
                            ->modalContent(fn (MasterKegiatan $record) => view('filament.modals.kegiatan-sub-kegiatan-tree', ['kegiatan' => $record]))
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
                SelectFilter::make('program_id')
                    ->label('Program Induk')
                    ->relationship('program', 'nama_program')
                    ->searchable()
                    ->preload(),
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
