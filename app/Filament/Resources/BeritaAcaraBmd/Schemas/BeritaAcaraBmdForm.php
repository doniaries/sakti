<?php

declare(strict_types=1);

namespace App\Filament\Resources\BeritaAcaraBmd\Schemas;

use App\Models\BukuInventaris;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BeritaAcaraBmdForm
{
    public static function configure(Schema $schema): Schema
    {
        $pegawaiOptions = User::query()->orderBy('name')->pluck('name', 'id')->all();

        return $schema
            ->components([
                Section::make('Informasi Dokumen Berita Acara')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('jenis')
                                    ->label('Jenis Berita Acara')
                                    ->options([
                                        'penggunaan' => 'Berita Acara Penggunaan BMD',
                                        'peminjaman' => 'Berita Acara Peminjaman Barang',
                                    ])
                                    ->default('penggunaan')
                                    ->required(),

                                TextInput::make('nomor_surat')
                                    ->label('Nomor Surat')
                                    ->placeholder('Contoh: 030/03/Parpora-2026')
                                    ->default(fn (): string => '030/   /Parpora-' . date('Y'))
                                    ->required()
                                    ->maxLength(100),

                                DatePicker::make('tanggal')
                                    ->label('Tanggal Berita Acara')
                                    ->displayFormat('d/m/Y')
                                    ->default(now())
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('kondisi_barang')
                                    ->label('Kondisi Barang Saat Diserahkan')
                                    ->options([
                                        'Baik' => 'Baik',
                                        'Kurang Baik' => 'Kurang Baik',
                                        'Rusak Berat' => 'Rusak Berat',
                                    ])
                                    ->default('Baik')
                                    ->required(),

                                TextInput::make('lokasi_penggunaan')
                                    ->label('Lokasi Penggunaan Default')
                                    ->default('Muaro Sijunjung')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ]),

                Section::make('Pihak Pertama & Pihak Kedua')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Section::make('Pihak Pertama (Yang Menyerahkan)')
                                    ->schema([
                                        Select::make('pihak_pertama_id')
                                            ->label('Pilih Pegawai (Opsional)')
                                            ->options($pegawaiOptions)
                                            ->searchable()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                                if (! $state) {
                                                    return;
                                                }
                                                $user = User::query()->find($state);
                                                if ($user) {
                                                    $set('pihak_pertama_nama', $user->name);
                                                    $set('pihak_pertama_nip', $user->nip ?? '');
                                                    $set('pihak_pertama_jabatan', $user->jabatan ?? 'Pengurus Barang');
                                                }
                                            }),
                                        TextInput::make('pihak_pertama_nama')
                                            ->label('Nama Lengkap')
                                            ->default('IMELDA SANGGETA, A. Md')
                                            ->required(),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('pihak_pertama_nip')
                                                    ->label('NIP')
                                                    ->default('19751009 200501 2 002'),
                                                TextInput::make('pihak_pertama_pangkat')
                                                    ->label('Pangkat / Golongan')
                                                    ->placeholder('Contoh: Penata III/c'),
                                            ]),
                                        TextInput::make('pihak_pertama_jabatan')
                                            ->label('Jabatan')
                                            ->default('Pengurus Barang'),
                                        TextInput::make('pihak_pertama_instansi')
                                            ->label('Instansi')
                                            ->default('Dinas Pariwisata Pemuda dan Olahraga'),
                                    ]),

                                Section::make('Pihak Kedua (Yang Menerima)')
                                    ->schema([
                                        Select::make('pihak_kedua_id')
                                            ->label('Pilih Pegawai Dinas')
                                            ->options($pegawaiOptions)
                                            ->searchable()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                                if (! $state) {
                                                    return;
                                                }
                                                $user = User::query()->find($state);
                                                if ($user) {
                                                    $set('pihak_kedua_nama', $user->name);
                                                    $set('pihak_kedua_nip', $user->nip ?? '-');
                                                    $set('pihak_kedua_jabatan', $user->jabatan ?? '');
                                                }
                                            }),
                                        TextInput::make('pihak_kedua_nama')
                                            ->label('Nama Lengkap')
                                            ->required(),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('pihak_kedua_nip')
                                                    ->label('NIP')
                                                    ->default('-'),
                                                TextInput::make('pihak_kedua_pangkat')
                                                    ->label('Pangkat / Golongan')
                                                    ->placeholder('Contoh: Penata Tk I / III/d'),
                                            ]),
                                        TextInput::make('pihak_kedua_jabatan')
                                            ->label('Jabatan'),
                                        TextInput::make('pihak_kedua_instansi')
                                            ->label('Instansi')
                                            ->default('Dinas Pariwisata Pemuda dan Olahraga'),
                                    ]),
                            ]),
                    ]),

                Section::make('Rincian Barang / Aset yang Diserahkan')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->label('Daftar Barang')
                            ->schema([
                                Select::make('buku_inventaris_id')
                                    ->label('Ambil dari Buku Inventaris (Opsional)')
                                    ->options(fn (): array => BukuInventaris::query()
                                        ->orderBy('nama_barang')
                                        ->limit(200)
                                        ->get()
                                        ->mapWithKeys(fn (BukuInventaris $item): array => [
                                            $item->id => "{$item->nama_barang} ({$item->kode_barang} - {$item->tahun_pembelian})",
                                        ])
                                        ->all())
                                    ->searchable()
                                    ->getSearchResultsUsing(fn (string $search): array => BukuInventaris::query()
                                        ->where('nama_barang', 'like', "%{$search}%")
                                        ->orWhere('kode_barang', 'like', "%{$search}%")
                                        ->orWhere('merk_type', 'like', "%{$search}%")
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(fn (BukuInventaris $item): array => [
                                            $item->id => "{$item->nama_barang} ({$item->kode_barang} - {$item->tahun_pembelian})",
                                        ])
                                        ->all())
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                        if (! $state) {
                                            return;
                                        }
                                        $aset = BukuInventaris::query()->find($state);
                                        if ($aset) {
                                            $nama = trim($aset->nama_barang . ($aset->merk_type ? ' ' . $aset->merk_type : ''));
                                            $set('nama_barang', $nama);
                                            $set('nilai_perolehan', (float) $aset->harga);
                                            $set('satuan', $aset->satuan ?: 'Unit');
                                        }
                                    })
                                    ->columnSpanFull(),

                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('nama_barang')
                                            ->label('Nama / Spesifikasi Barang')
                                            ->required()
                                            ->columnSpan(2),
                                        TextInput::make('jumlah')
                                            ->label('Jumlah')
                                            ->numeric()
                                            ->default(1)
                                            ->required(),
                                        TextInput::make('satuan')
                                            ->label('Satuan')
                                            ->default('Unit')
                                            ->required(),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('nilai_perolehan')
                                            ->label('Nilai Perolehan (Rp)')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->required(),
                                        TextInput::make('lokasi_penggunaan')
                                            ->label('Lokasi Penggunaan')
                                            ->default('Muaro Sijunjung')
                                            ->required(),
                                    ]),

                                Textarea::make('keterangan')
                                    ->label('Keterangan Tambahan (Opsional)')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Barang')
                            ->collapsible(),
                    ]),

                Section::make('Mengetahui (Kepala Dinas)')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('kepala_dinas_nama')
                                    ->label('Nama Kepala Dinas')
                                    ->default('AFRINELDI, SH')
                                    ->required(),
                                TextInput::make('kepala_dinas_nip')
                                    ->label('NIP Kepala Dinas')
                                    ->default('19720407 199202 1 002')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
