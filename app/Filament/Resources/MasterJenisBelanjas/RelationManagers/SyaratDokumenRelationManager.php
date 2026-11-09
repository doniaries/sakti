<?php

namespace App\Filament\Resources\MasterJenisBelanjas\RelationManagers;

use App\Models\MasterSyaratBelanja;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SyaratDokumenRelationManager extends RelationManager
{
    protected static string $relationship = 'syaratBelanjas';

    protected static ?string $title = 'Daftar Syarat Dokumen SOP';

    protected static ?string $modelLabel = 'Syarat Dokumen';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rincian Dokumen Persyaratan')
                    ->schema([
                        TextInput::make('nama_syarat')
                            ->label('Nama Dokumen Persyaratan')
                            ->placeholder('Contoh: Faktur / Nota Toko Asli')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('keterangan')
                            ->label('Petunjuk / Keterangan Dokumen (Opsional)')
                            ->placeholder('Contoh: Scan dokumen PDF asli bertandatangan dan berstempel')
                            ->rows(2),

                        Toggle::make('is_required')
                            ->label('Wajib Diunggah')
                            ->helperText('Jika diaktifkan, berkas ini menjadi syarat mutlak (wajib). Jika tidak, berkas bersifat opsional.')
                            ->default(true),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('urutan')
            ->defaultSort('urutan')
            ->columns([
                TextColumn::make('nama_syarat')
                    ->label('Nama Dokumen Persyaratan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (MasterSyaratBelanja $record): ?string => $record->keterangan)
                    ->wrap(),

                ToggleColumn::make('is_required')
                    ->label('Wajib')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Syarat Dokumen')
                    ->icon('heroicon-o-plus')
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data): array {
                        $parent = $this->getOwnerRecord();
                        $data['jenis_belanja'] = $parent->kode;
                        $data['metode_pengadaan'] = $parent->metode_pengadaan;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ]);
    }
}

