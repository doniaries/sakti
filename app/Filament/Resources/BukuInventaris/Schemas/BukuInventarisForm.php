<?php

declare(strict_types=1);

namespace App\Filament\Resources\BukuInventaris\Schemas;

use App\Support\FormatHelper;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BukuInventarisForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identitas Barang & Kodefikasi SIMBADA/KIB')
                    ->description('Data nomor urut, kodefikasi barang dan register inventaris resmi.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('nomor_urut')
                            ->label('Nomor Urut')
                            ->numeric()
                            ->required(),

                        TextInput::make('kode_barang')
                            ->label('Kode Barang')
                            ->placeholder('Contoh: 01.01.01.05.001')
                            ->required(),

                        TextInput::make('register')
                            ->label('Register')
                            ->placeholder('Contoh: 000001 atau 000001 s/d 000004'),

                        TextInput::make('nama_barang')
                            ->label('Nama / Jenis Barang')
                            ->placeholder('Contoh: Bangunan Gedung Kantor, Station Wagon Toyota, dll.')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Spesifikasi Teknis & Kondisi')
                    ->description('Detail merek, nomor sertifikat/mesin, bahan fisik, ukuran, dan kondisi barang.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('merk_type')
                            ->label('Merk / Type')
                            ->placeholder('Contoh: Toyota / Kijang Inova, Tiger / TG4800E'),

                        TextInput::make('nomor_sertifikat_pabrik_chasis_mesin')
                            ->label('No. Sertifikat / Pabrik / Chasis / Mesin')
                            ->placeholder('Nomor dokumen legalitas atau nomor mesin'),

                        TextInput::make('bahan')
                            ->label('Bahan')
                            ->placeholder('Contoh: Besi, Kayu, Beton, Campuran'),

                        TextInput::make('ukuran_konstruksi')
                            ->label('Ukuran / Konstruksi')
                            ->placeholder('Contoh: 3080, 1998, 782.5'),

                        TextInput::make('satuan')
                            ->label('Satuan')
                            ->placeholder('Contoh: M2, CC, Unit, Buah, Set'),

                        Select::make('keadaan_barang')
                            ->label('Keadaan Barang')
                            ->options([
                                'Baik' => 'Baik (B)',
                                'Kurang Baik' => 'Kurang Baik (KB)',
                                'Rusak Berat' => 'Rusak Berat (RB)',
                            ])
                            ->default('Baik')
                            ->required(),
                    ]),

                Section::make('Perolehan & Nilai Aset')
                    ->description('Asal usul perolehan, tahun pengadaan, volume jumlah fisik dan nilai harga perolehan.')
                    ->columns(3)
                    ->schema([
                        Select::make('asal_perolehan')
                            ->label('Asal / Cara Perolehan')
                            ->options([
                                'Pembelian' => 'Pembelian',
                                'Mutasi' => 'Mutasi',
                                'Hibah' => 'Hibah',
                            ])
                            ->default('Pembelian')
                            ->required(),

                        TextInput::make('tahun_pembelian')
                            ->label('Tahun Pembelian / Perolehan')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(2100)
                            ->placeholder('Contoh: 2024'),

                        TextInput::make('jumlah_barang')
                            ->label('Jumlah Barang')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        FormatHelper::rupiahInput('harga', 'Harga Perolehan (Rp)')
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('keterangan')
                            ->label('Keterangan Tambahan')
                            ->placeholder('Catatan peruntukan barang, lokasi pemakaian, status peminjaman, dll.')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
