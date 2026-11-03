<?php

declare(strict_types=1);

namespace App\Filament\Resources\PengajuanSpjs\Schemas;

use App\Models\Bidang;
use App\Models\KodeRekening;
use App\Models\MasterBarang;
use App\Models\MasterKegiatan;
use App\Models\MasterSubKegiatan;
use App\Models\PengajuanSpj;
use App\Models\StokBarang;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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
                            ->live()
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

                Section::make('Rincian Barang Persediaan (Khusus Barang yang Memiliki Stok Tersedia)')
                    ->description('Hanya barang yang memiliki stok tersedia (> 0) pada Bidang Pengusul yang dapat dipilih dan diajukan dalam Surat Pesanan.')
                    ->schema([
                        Repeater::make('details')
                            ->relationship()
                            ->label('Daftar Barang Belanja (Stok Tersedia)')
                            ->schema([
                                Select::make('kode_rekening_id')
                                    ->label('Rekening Belanja')
                                    ->options(function (callable $get) {
                                        $bidangId = $get('../../bidang_id');

                                        return KodeRekening::whereHas('masterBarangs.stokBarangs', function ($query) use ($bidangId) {
                                            $query->where('stok_tersedia', '>', 0);
                                            if ($bidangId) {
                                                $query->where('bidang_id', $bidangId);
                                            }
                                        })
                                            ->get()
                                            ->pluck('label', 'id')
                                            ->all();
                                    })
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('master_barang_id', null);
                                        $set('stok_tersedia', 0);
                                        $set('harga_satuan', 0);
                                        $set('subtotal', 0);
                                    })
                                    ->required()
                                    ->columnSpan(3),

                                Select::make('master_barang_id')
                                    ->label('Pilih Barang (Stok > 0)')
                                    ->options(function (callable $get) {
                                        $bidangId = $get('../../bidang_id');
                                        $kodeRekeningId = $get('kode_rekening_id');
                                        $currentBarangId = $get('master_barang_id');

                                        $query = MasterBarang::with(['satuan', 'stokBarangs' => function ($q) use ($bidangId) {
                                            if ($bidangId) {
                                                $q->where('bidang_id', $bidangId);
                                            }
                                        }]);

                                        if ($kodeRekeningId) {
                                            $query->where('kode_rekening_id', $kodeRekeningId);
                                        }

                                        $query->where(function ($q) use ($bidangId, $currentBarangId) {
                                            $q->whereHas('stokBarangs', function ($sq) use ($bidangId) {
                                                $sq->where('stok_tersedia', '>', 0);
                                                if ($bidangId) {
                                                    $sq->where('bidang_id', $bidangId);
                                                }
                                            });

                                            if ($currentBarangId) {
                                                $q->orWhere('id', $currentBarangId);
                                            }
                                        });

                                        return $query->get()->mapWithKeys(function (MasterBarang $barang) use ($bidangId) {
                                            $stok = $bidangId
                                                ? (int) $barang->stokBarangs->where('bidang_id', (int) $bidangId)->sum('stok_tersedia')
                                                : (int) $barang->stokBarangs->sum('stok_tersedia');
                                            $satuan = $barang->satuan?->nama ?? 'Unit';
                                            $harga = number_format((float) $barang->harga_satuan, 0, ',', '.');

                                            return [
                                                $barang->id => "{$barang->nama_barang} ({$satuan}) — [Stok: {$stok}] — Rp {$harga}",
                                            ];
                                        })->all();
                                    })
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set, callable $get, ?string $state) {
                                        if ($state) {
                                            $bidangId = $get('../../bidang_id');
                                            $barang = MasterBarang::with('satuan')->find($state);
                                            if ($barang) {
                                                $stokQuery = StokBarang::where('master_barang_id', $state);
                                                if ($bidangId) {
                                                    $stokQuery->where('bidang_id', $bidangId);
                                                }
                                                $stokTersedia = (int) $stokQuery->sum('stok_tersedia');

                                                $harga = (float) $barang->harga_satuan;
                                                $qty = min((int) ($get('kuantitas') ?: 1), max(1, $stokTersedia));

                                                if (! $get('kode_rekening_id') && $barang->kode_rekening_id) {
                                                    $set('kode_rekening_id', $barang->kode_rekening_id);
                                                }

                                                $set('stok_tersedia', $stokTersedia);
                                                $set('kuantitas', $qty);
                                                $set('harga_satuan', $harga);
                                                $set('subtotal', $qty * $harga);
                                                $set('kuantitas_realisasi', $qty);
                                                $set('harga_realisasi', $harga);
                                                $set('subtotal_realisasi', $qty * $harga);
                                            }
                                        } else {
                                            $set('stok_tersedia', 0);
                                            $set('harga_satuan', 0);
                                            $set('subtotal', 0);
                                        }
                                    })
                                    ->required()
                                    ->columnSpan(3),

                                TextInput::make('stok_tersedia')
                                    ->label('Stok Tersedia')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->default(0)
                                    ->formatStateUsing(function (callable $get): int {
                                        $barangId = $get('master_barang_id');
                                        if (! $barangId) {
                                            return 0;
                                        }
                                        $bidangId = $get('../../bidang_id');
                                        $stokQuery = StokBarang::where('master_barang_id', $barangId);
                                        if ($bidangId) {
                                            $stokQuery->where('bidang_id', $bidangId);
                                        }

                                        return (int) $stokQuery->sum('stok_tersedia');
                                    })
                                    ->columnSpan(1),

                                TextInput::make('kuantitas')
                                    ->label('Vol Pesanan')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(function (callable $get): int {
                                        $stok = (int) ($get('stok_tersedia') ?: 0);

                                        return $stok > 0 ? $stok : 1;
                                    })
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        $stokTersedia = (int) ($get('stok_tersedia') ?: 0);
                                        $qty = (int) ($state ?: 0);

                                        if ($stokTersedia > 0 && $qty > $stokTersedia) {
                                            $qty = $stokTersedia;
                                            $set('kuantitas', $qty);

                                            Notification::make()
                                                ->danger()
                                                ->title('Melebihi Stok Tersedia')
                                                ->body("Volume pesanan tidak boleh melebihi stok barang yang tersedia ({$stokTersedia}).")
                                                ->send();
                                        }

                                        $harga = (float) ($get('harga_satuan') ?: 0);
                                        $set('subtotal', $qty * $harga);
                                        $set('kuantitas_realisasi', $qty);
                                        $set('subtotal_realisasi', $qty * $harga);
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
                                    ->columnSpan(2),

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
                                    ->columnSpan(2),

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
                                    ->columnSpan(2),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Item Barang (Stok Tersedia)'),
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
