<?php

namespace App\Filament\Resources\MasterSyaratBelanjas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MasterSyaratBelanjaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Syarat Dokumen Belanja')
                    ->description('Kelola dokumen persyaratan yang wajib atau opsional diunggah pada pengajuan SPJ.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('jenis_belanja')
                                    ->label('Jenis Belanja')
                                    ->options([
                                        'semua' => 'Semua Jenis Belanja',
                                        'barang_jasa' => 'Belanja Barang & Jasa',
                                        'makan_minum' => 'Makan & Minum Rapat',
                                        'sppd' => 'Perjalanan Dinas (SPPD)',
                                        'bbm' => 'BBM (Bahan Bakar Minyak)',
                                    ])
                                    ->default('barang_jasa')
                                    ->required(),

                                Select::make('metode_pengadaan')
                                    ->label('Metode Pengadaan')
                                    ->options([
                                        'semua' => 'Semua Metode (E-Katalog & Non-Katalog)',
                                        'e_katalog' => 'E-Katalog (E-Purchasing LKPP)',
                                        'non_katalog' => 'Non-Katalog (Pengadaan Langsung)',
                                    ])
                                    ->default('non_katalog')
                                    ->required(),
                            ]),

                        TextInput::make('nama_syarat')
                            ->label('Nama Dokumen Syarat')
                            ->placeholder('Contoh: Daftar Hadir Rapat Bertandatangan')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('keterangan')
                            ->label('Petunjuk / Keterangan Dokumen')
                            ->placeholder('Contoh: Scan dokumen PDF asli bertandatangan dan berstempel')
                            ->rows(3),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('urutan')
                                    ->label('Nomor Urut Tampil')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),

                                Toggle::make('is_required')
                                    ->label('Wajib Diunggah')
                                    ->helperText('Jika aktif, berkas ini menjadi syarat mutlak verifikasi SPJ.')
                                    ->default(true),

                                Toggle::make('aktif')
                                    ->label('Status Aktif')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
