<?php

namespace App\Filament\Resources\Bidangs\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PegawaiRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Daftar Pegawai / Staf';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                TextInput::make('nip')
                    ->label('NIP')
                    ->maxLength(30),

                Select::make('jabatan_id')
                    ->label('Jabatan')
                    ->relationship('jabatan', 'nama')
                    ->searchable()
                    ->preload(),

                Select::make('status_kerja')
                    ->label('Tipe Kerja')
                    ->options([
                        'wfo' => 'WFO (Work From Office)',
                        'wfh' => 'WFH (Work From Home)',
                        'fleksibel' => 'Fleksibel (Hybrid)',
                    ])
                    ->default('wfo')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('jabatan.nama')
                    ->label('Jabatan')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('atasan.name')
                    ->label('Atasan Langsung')
                    ->placeholder('-'),

                TextColumn::make('status_kerja')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'wfh' => 'warning',
                        'fleksibel' => 'info',
                        default => 'success',
                    })
                    ->formatStateUsing(fn ($state) => strtoupper($state ?? 'WFO')),

                ToggleColumn::make('aktif')
                    ->label('Status Aktif'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AssociateAction::make()
                    ->label('Hubungkan Pegawai ke Bidang Ini')
                    ->preloadRecordVideos(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make()
                    ->label('Keluarkan dari Bidang'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make()
                        ->label('Keluarkan Terpilih'),
                ]),
            ]);
    }
}
