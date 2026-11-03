<?php

namespace App\Filament\Resources\PengajuanSpjs\Schemas;

use App\Models\Bidang;
use App\Models\KodeRekening;
use App\Models\MasterBarang;
use App\Models\MasterKegiatan;
use App\Models\MasterSubKegiatan;
use App\Models\PengajuanSpj;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PengajuanSpjForm
{
    public static function configure(Schema $schema): Schema
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $schema
            ->components([
                Section::make('Informasi Pengajuan & Perencanaan Belanja')
                    ->description('Tahap 1: Pengisian rencana kebutuhan barang sebelum pembelian fisik')
                    ->columns(3)
                    ->schema([
                        TextInput::make('nomor_pengajuan')
                            ->label('Nomor Pengajuan SPJ')
                            ->default(fn () => PengajuanSpj::generateNomorPengajuan())
                            ->required()
                            ->readOnly(),

                        DatePicker::make('tanggal_pengajuan')
                            ->label('Tanggal Pengajuan')
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->required(),

                        Select::make('bidang_id')
                            ->label('Bidang Pengusul')
                            ->options(fn () => Bidang::pluck('nama', 'id')->all())
                            ->default(fn () => $user?->bidang_id)
                            ->searchable()
                            ->required(),

                        Select::make('kegiatan_id')
                            ->label('Kegiatan DPA')
                            ->options(fn () => MasterKegiatan::all()->pluck('label', 'id')->all())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('sub_kegiatan_id', null))
                            ->required()
                            ->columnSpan(1),

                        Select::make('sub_kegiatan_id')
                            ->label('Sub Kegiatan')
                            ->options(function (callable $get) {
                                $kegiatanId = $get('kegiatan_id');
                                if (!$kegiatanId) {
                                    return [];
                                }
                                return MasterSubKegiatan::where('kegiatan_id', $kegiatanId)
                                    ->get()
                                    ->pluck('label', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->required()
                            ->columnSpan(2),

                        Hidden::make('user_id')
                            ->default(fn () => Auth::id()),
                    ]),

                Section::make('Rincian Item Barang Yang Dipesan')
                    ->description('Daftar barang kebutuhan kegiatan yang akan dicantumkan pada Nota Pesanan')
                    ->schema([
                        Repeater::make('details')
                            ->relationship('details')
                            ->schema([
                                Select::make('kode_rekening_id')
                                    ->label('Rekening Belanja')
                                    ->options(fn () => KodeRekening::all()->pluck('label', 'id')->all())
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(3),

                                Select::make('master_barang_id')
                                    ->label('Pilih Barang Persediaan')
                                    ->options(fn () => MasterBarang::with('satuan')->get()->pluck('label', 'id')->all())
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set, callable $get, ?string $state) {
                                        if ($state) {
                                            $barang = MasterBarang::find($state);
                                            if ($barang) {
                                                $set('harga_satuan', (float) $barang->harga_satuan);
                                                $qty = (int) ($get('kuantitas') ?: 1);
                                                $set('kuantitas', $qty);
                                                $set('subtotal', $qty * (float) $barang->harga_satuan);
                                            }
                                        }
                                    })
                                    ->required()
                                    ->columnSpan(4),

                                TextInput::make('kuantitas')
                                    ->label('Vol / Qty')
                                    ->numeric()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (callable $set, callable $get, ?int $state) {
                                        $harga = (float) ($get('harga_satuan') ?: 0);
                                        $set('subtotal', ((int) $state) * $harga);
                                    })
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('harga_satuan')
                                    ->label('Harga Satuan (Rp)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (callable $set, callable $get, ?float $state) {
                                        $qty = (int) ($get('kuantitas') ?: 0);
                                        $set('subtotal', $qty * ((float) $state));
                                    })
                                    ->required()
                                    ->columnSpan(3),

                                TextInput::make('subtotal')
                                    ->label('Subtotal (Rp)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->columnSpan(12),
                            ])
                            ->columns(12)
                            ->live()
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $items = $get('details') ?: [];
                                $total = 0;
                                foreach ($items as $item) {
                                    $qty = (int) ($item['kuantitas'] ?? 0);
                                    $harga = (float) ($item['harga_satuan'] ?? 0);
                                    $total += ($qty * $harga);
                                }
                                $set('total_nilai_rencana', $total);
                            })
                            ->minItems(1)
                            ->collapsible()
                            ->defaultItems(1),

                        TextInput::make('total_nilai_rencana')
                            ->label('Total Nilai Rencana Belanja (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly()
                            ->helperText('Otomatis dihitung dari akumulasi rincian barang di atas.'),
                    ]),

                Section::make('Tahap 2: Realisasi Pembelian & Unggah Berkas Bukti Toko')
                    ->description('Diisi setelah staf selesai membeli barang di penyedia/toko fisik')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama_toko')
                            ->label('Nama Toko / Rekanan Penyedia')
                            ->placeholder('Contoh: Toko Buku & ATK Sejahtera'),

                        TextInput::make('penerima_nama')
                            ->label('Nama Penerima Uang / Kasir')
                            ->placeholder('Contoh: Budi Santoso (Pemilik Toko)'),

                        Textarea::make('alamat_toko')
                            ->label('Alamat Lengkap Toko')
                            ->placeholder('Jl. Sudirman No. 45')
                            ->columnSpanFull(),

                        TextInput::make('total_nilai_realisasi')
                            ->label('Total Nilai Realisasi Faktur (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->helperText('Isi nominal sesungguhnya sesuai total nota/faktur toko.'),

                        FileUpload::make('file_nota_penjual')
                            ->label('Scan / Foto Faktur Asli Toko')
                            ->image()
                            ->directory('spj-nota-penjual')
                            ->helperText('Wajib diunggah untuk membuktikan transaksi riil belanja.'),

                        FileUpload::make('file_dokumentasi_barang')
                            ->label('Foto Dokumentasi Fisik Barang')
                            ->image()
                            ->directory('spj-dokumentasi-barang')
                            ->helperText('Wajib mutlak apabila total nilai belanja di atas Rp 2.000.000,-.'),

                        Textarea::make('justifikasi_selisih')
                            ->label('Catatan Justifikasi Selisih / Deviasi')
                            ->placeholder('Jelaskan alasan jika terdapat perbedaan antara rencana dan realisasi harga/volume.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
