<?php

declare(strict_types=1);

namespace App\Filament\Resources\BukuInventaris\Schemas;

use App\Models\BukuInventaris;
use App\Support\FormatHelper;
use Filament\Forms\Components\FileUpload;
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
                            ->default(fn (): int => ((int) BukuInventaris::max('nomor_urut')) + 1)
                            ->helperText('Otomatis melanjutkan nomor urut terakhir.')
                            ->required(),

                        TextInput::make('kode_barang')
                            ->label('Kode Barang')
                            ->placeholder('01.01.01.05.001')
                            ->mask('99.99.99.99.999')
                            ->regex('/^\d{2}\.\d{2}\.\d{2}\.\d{2}\.\d{3}$/')
                            ->validationMessages([
                                'regex' => 'Format kode barang harus 99.99.99.99.999 (contoh: 01.01.01.05.001)',
                            ])
                            ->helperText('Format standar Permendagri 108 / SIMBADA memakai titik.')
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
                        Select::make('merk_type')
                            ->label('Merk / Type')
                            ->searchable()
                            ->placeholder('Pilih atau tambah Merk / Type')
                            ->options(fn (): array => BukuInventaris::query()
                                ->whereNotNull('merk_type')
                                ->where('merk_type', '!=', '')
                                ->where('merk_type', '!=', '-')
                                ->distinct()
                                ->orderBy('merk_type')
                                ->pluck('merk_type', 'merk_type')
                                ->all())
                            ->createOptionForm([
                                TextInput::make('value')
                                    ->label('Merk / Type Baru')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(fn (array $data): string => trim((string) $data['value'])),

                        TextInput::make('nomor_sertifikat_pabrik_chasis_mesin')
                            ->label('No. Sertifikat / Pabrik / Chasis / Mesin')
                            ->placeholder('Nomor dokumen legalitas atau nomor mesin'),

                        Select::make('bahan')
                            ->label('Bahan')
                            ->searchable()
                            ->placeholder('Pilih atau tambah Bahan')
                            ->options(fn (): array => BukuInventaris::query()
                                ->whereNotNull('bahan')
                                ->where('bahan', '!=', '')
                                ->where('bahan', '!=', '-')
                                ->distinct()
                                ->orderBy('bahan')
                                ->pluck('bahan', 'bahan')
                                ->all())
                            ->createOptionForm([
                                TextInput::make('value')
                                    ->label('Nama Bahan Baru')
                                    ->required()
                                    ->maxLength(100),
                            ])
                            ->createOptionUsing(fn (array $data): string => trim((string) $data['value'])),

                        TextInput::make('ukuran_konstruksi')
                            ->label('Ukuran / Konstruksi (P, S, D)')
                            ->placeholder('Contoh: 782.5, 1498, atau 36')
                            ->helperText('Konstruksi: P = Permanen, S/SP = Semi Permanen, D = Darurat. Kendaraan: CC. Luas: M2.'),

                        Select::make('satuan')
                            ->label('Satuan')
                            ->searchable()
                            ->placeholder('Pilih atau tambah Satuan')
                            ->options(fn (): array => BukuInventaris::query()
                                ->whereNotNull('satuan')
                                ->where('satuan', '!=', '')
                                ->where('satuan', '!=', '-')
                                ->distinct()
                                ->orderBy('satuan')
                                ->pluck('satuan', 'satuan')
                                ->all())
                            ->createOptionForm([
                                TextInput::make('value')
                                    ->label('Nama Satuan Baru')
                                    ->required()
                                    ->maxLength(50),
                            ])
                            ->createOptionUsing(fn (array $data): string => trim((string) $data['value'])),

                        Select::make('keadaan_barang')
                            ->label('Keadaan Barang')
                            ->options([
                                'Baik' => 'Baik (B) - Hijau',
                                'Kurang Baik' => 'Kurang Baik (KB) - Orange',
                                'Rusak Berat' => 'Rusak Berat (RB) - Merah',
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

                        FileUpload::make('photo')
                            ->label('Foto Barang / Aset')
                            ->image()
                            ->disk('public')
                            ->directory('buku-inventaris/photos')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth('1024')
                            ->imageResizeTargetHeight('1024')
                            ->helperText('Format JPG/PNG/WEBP, maks. 2MB. Gambar otomatis diperkecil (maks. 1024px) untuk menghemat ruang server.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
