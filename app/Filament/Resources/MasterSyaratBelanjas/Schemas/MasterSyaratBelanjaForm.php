<?php

namespace App\Filament\Resources\MasterSyaratBelanjas\Schemas;

use App\Models\MasterSyaratBelanja;
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
                                Select::make('metode_pengadaan')
                                    ->label('Metode Pengadaan')
                                    ->options([
                                        'non_katalog' => 'Non-Katalog (Pengadaan Langsung)',
                                        'e_katalog' => 'E-Katalog (E-Purchasing LKPP)',
                                        'semua' => 'Semua Metode (Lintas Pengadaan)',
                                    ])
                                    ->default('non_katalog')
                                    ->required(),

                                Select::make('jenis_belanja')
                                    ->label('Jenis Pekerjaan / Belanja')
                                    ->options(function () {
                                        $options = [
                                            'barang_jasa' => 'Non-Katalog: Belanja Barang & Jasa',
                                            'makan_minum' => 'Non-Katalog: Makan & Minum Rapat',
                                            'sppd' => 'Non-Katalog: Perjalanan Dinas (SPPD)',
                                            'bbm' => 'Non-Katalog: BBM (Bahan Bakar Minyak)',
                                            'e_katalog' => 'E-Katalog: Pengadaan Paket E-Purchasing',
                                            'semua' => 'Dokumen Umum / Lintas Belanja',
                                        ];

                                        $customs = MasterSyaratBelanja::query()
                                            ->whereNotIn('jenis_belanja', array_keys($options))
                                            ->distinct()
                                            ->pluck('jenis_belanja');

                                        foreach ($customs as $c) {
                                            $options[$c] = 'Pekerjaan: ' . ucwords(str_replace('_', ' ', $c));
                                        }

                                        return $options;
                                    })
                                    ->searchable()
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
