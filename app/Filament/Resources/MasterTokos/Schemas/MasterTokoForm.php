<?php

namespace App\Filament\Resources\MasterTokos\Schemas;

use App\Models\MasterBank;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MasterTokoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Toko / Rekanan Penyedia')
                    ->compact()
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama_toko')
                            ->label('Nama Toko / Pemasok')
                            ->placeholder('Contoh: Toko Rilex Abadi')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('nama_pemilik')
                            ->label('Nama Pemilik Toko')
                            ->placeholder('Contoh: Agusrizal')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('telepon')
                            ->label('No. Telepon')
                            ->placeholder('Contoh: 0812-3456-7890')
                            ->maxLength(50),

                        TextInput::make('npwp')
                            ->label('NPWP Toko (Opsional)')
                            ->placeholder('Contoh: 01.234.567.8-201.000')
                            ->maxLength(50),

                        Select::make('master_bank_id')
                            ->label('Nama Bank')
                            ->placeholder('Pilih Bank')
                            ->options(fn () => MasterBank::where('is_active', true)->pluck('nama_bank', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('kode_bank')
                                    ->label('Kode Bank')
                                    ->placeholder('Contoh: 118')
                                    ->maxLength(20),
                                TextInput::make('nama_bank')
                                    ->label('Nama Bank')
                                    ->placeholder('Contoh: Bank Nagari (BPD Sumbar)')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return MasterBank::create([
                                    'kode_bank' => $data['kode_bank'] ?? null,
                                    'nama_bank' => $data['nama_bank'],
                                    'is_active' => true,
                                ])->id;
                            }),

                        TextInput::make('nomor_rekening')
                            ->label('No. Rekening Bank Toko')
                            ->placeholder('Contoh: 0700.0210.21699-2')
                            ->maxLength(100),

                        Textarea::make('alamat')
                            ->label('Alamat Toko')
                            ->placeholder('Contoh: Pasar Inpres Muaro Sijunjung')
                            ->required()
                            ->rows(2)
                            ->columnSpan(2),

                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
