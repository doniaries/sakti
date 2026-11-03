<?php

declare(strict_types=1);

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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
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
                Section::make('Tahap 1: Pengajuan Surat Pesanan ke Kepala Bidang (Kabid) selaku PPTK')
                    ->description('Staf menyusun Surat Pesanan untuk diajukan dan ditandatangani (TTD) oleh Kepala Bidang selaku PPTK sebelum pembelian barang dan pemrosesan Surat Kwitansi.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nomor_pengajuan')
                            ->label('Nomor Pengajuan / Surat Pesanan')
                            ->default(fn () => PengajuanSpj::generateNomorPengajuan())
                            ->required()
                            ->readOnly(),

                        DatePicker::make('tanggal_pengajuan')
                            ->label('Tanggal Surat Pesanan')
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->required(),

                        Select::make('bidang_id')
                            ->label('Bidang Pengusul (PPTK: Kepala Bidang)')
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
                            ->label('Sub Kegiatan DPA')
                            ->options(function (callable $get) {
                                $kegiatanId = $get('kegiatan_id');
                                if (! $kegiatanId) {
                                    return [];
                                }

                                return MasterSubKegiatan::where('kegiatan_id', $kegiatanId)
                                    ->get()
                                    ->pluck('label', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('nama_toko')
                            ->label('Nama Toko / Rekanan Penyedia')
                            ->placeholder('Contoh: Toko ATK Sinar Jaya')
                            ->maxLength(255),

                        TextInput::make('penerima_nama')
                            ->label('Nama Pimpinan / Penerima Kwitansi')
                            ->maxLength(255),

                        TextInput::make('penerima_identitas')
                            ->label('NPWP / NIK / Jabatan Rekanan')
                            ->maxLength(100),

                        TextInput::make('nomor_bku')
                            ->label('Nomor BKU (Diisi Bendahara saat Pencairan)')
                            ->maxLength(100),

                        Textarea::make('alamat_toko')
                            ->label('Alamat Lengkap Toko / Rekanan')
                            ->rows(2)
                            ->columnSpan(2),

                        Hidden::make('user_id')
                            ->default(fn () => Auth::id()),
                    ]),

                Section::make('Rincian Barang pada Surat Pesanan & Realisasi Kwitansi')
                    ->description('Pilih kode rekening dan barang persediaan untuk Surat Pesanan. Kolom Realisasi digunakan untuk memproses nominal Surat Kwitansi setelah Surat Pesanan di-TTD Kabid (PPTK).')
                    ->schema([
                        Repeater::make('details')
                            ->relationship()
                            ->label('Daftar Barang Belanja')
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
                                            $barang = MasterBarang::with('satuan')->find($state);
                                            if ($barang) {
                                                $harga = (float) $barang->harga_satuan;
                                                $qty = (int) ($get('kuantitas') ?: 1);
                                                $set('harga_satuan', $harga);
                                                $set('subtotal', $qty * $harga);
                                                $set('kuantitas_realisasi', $qty);
                                                $set('harga_realisasi', $harga);
                                                $set('subtotal_realisasi', $qty * $harga);
                                            }
                                        }
                                    })
                                    ->required()
                                    ->columnSpan(3),

                                TextInput::make('kuantitas')
                                    ->label('Vol Pesanan')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        $qty = (int) ($get('kuantitas') ?: 0);
                                        $harga = (float) ($get('harga_satuan') ?: 0);
                                        $set('subtotal', $qty * $harga);
                                        if (! $get('kuantitas_realisasi')) {
                                            $set('kuantitas_realisasi', $qty);
                                            $set('subtotal_realisasi', $qty * $harga);
                                        }
                                    })
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('harga_satuan')
                                    ->label('Harga Pesanan (Rp)')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        $qty = (int) ($get('kuantitas') ?: 0);
                                        $harga = (float) ($get('harga_satuan') ?: 0);
                                        $set('subtotal', $qty * $harga);
                                    })
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('subtotal')
                                    ->label('Subtotal Pesanan (Rp)')
                                    ->numeric()
                                    ->default(0)
                                    ->readOnly()
                                    ->columnSpan(3),

                                TextInput::make('kuantitas_realisasi')
                                    ->label('Vol Realisasi Kwitansi')
                                    ->numeric()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        $qty = (int) ($get('kuantitas_realisasi') ?: 0);
                                        $harga = (float) ($get('harga_realisasi') ?: 0);
                                        $set('subtotal_realisasi', $qty * $harga);
                                    })
                                    ->columnSpan(1),

                                TextInput::make('harga_realisasi')
                                    ->label('Harga Aktual Kwitansi (Rp)')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        $qty = (int) ($get('kuantitas_realisasi') ?: 0);
                                        $harga = (float) ($get('harga_realisasi') ?: 0);
                                        $set('subtotal_realisasi', $qty * $harga);
                                    })
                                    ->columnSpan(2),

                                TextInput::make('subtotal_realisasi')
                                    ->label('Subtotal Kwitansi (Rp)')
                                    ->numeric()
                                    ->default(0)
                                    ->readOnly()
                                    ->columnSpan(3),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Item Barang Pesanan'),
                    ]),

                Section::make('Tahap 2: Proses Surat Kwitansi & Unggah Bukti (Setelah Surat Pesanan Di-TTD Kabid/PPTK)')
                    ->description('Bagian ini diproses setelah Surat Pesanan ditandatangani oleh Kabid selaku PPTK dan barang telah dibeli. Dokumentasi fisik barang WAJIB diunggah apabila nilai > Rp 2.000.000,-')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('file_nota_penjual')
                            ->label('Unggah Faktur / Nota Asli Toko (Dasar Surat Kwitansi)')
                            ->directory('spj/nota-penjual')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120),

                        FileUpload::make('file_dokumentasi_barang')
                            ->label('Unggah Foto Dokumentasi Fisik Barang (Wajib jika > Rp 2 Jt)')
                            ->directory('spj/dokumentasi-barang')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120),

                        Textarea::make('justifikasi_selisih')
                            ->label('Catatan Penjelasan / Justifikasi Selisih (Wajib diisi bila nilai Kwitansi berbeda dengan Surat Pesanan)')
                            ->rows(2)
                            ->columnSpan(2),

                        Placeholder::make('info_status')
                            ->label('Status Tahapan Dokumen Saat Ini')
                            ->content(fn (?PengajuanSpj $record) => $record ? $record->status_label : 'Draft Surat Pesanan Baru')
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
