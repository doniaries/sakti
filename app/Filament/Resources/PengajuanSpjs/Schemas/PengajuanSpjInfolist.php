<?php

namespace App\Filament\Resources\PengajuanSpjs\Schemas;

use App\Models\Bidang;
use App\Models\DetailPengajuanSpj;
use App\Models\PengajuanSpj;
use App\Support\FormatHelper;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\TextSize;

class PengajuanSpjInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                // Bagian Informasi Pengajuan (Tanpa Heading Teks agar Lapang dan Bersih)
                Section::make()
                    ->columnSpanFull()
                    ->compact()
                    ->schema([
                        TextEntry::make('nomor_pengajuan')
                            ->label('No. Pengajuan')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('status')
                            ->label('Status SPJ')
                            ->badge()
                            ->color(fn(PengajuanSpj $record): string => $record->status_color)
                            ->formatStateUsing(fn(PengajuanSpj $record): string => $record->status_label),

                        TextEntry::make('user.name')
                            ->label('Pemohon')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('bidang.nama')
                            ->label('Bidang')
                            ->badge()
                            ->color(fn(?string $state): string => Bidang::badgeColor($state)),

                        TextEntry::make('tanggal_pengajuan')
                            ->label('Tgl Pengajuan')
                            ->date('d/m/Y'),

                        TextEntry::make('nama_penyedia')
                            ->label('Penyedia')
                            ->badge()
                            ->color('success')
                            ->state(fn(PengajuanSpj $record): string => $record->nama_toko ?: ($record->masterToko?->nama_toko ?: '-')),

                        // Kegiatan & Sub Kegiatan dibuat Sebaris
                        TextEntry::make('kegiatan.nama_kegiatan')
                            ->label('Kegiatan')
                            ->columnSpan(['default' => 2, 'sm' => 3, 'md' => 3]),

                        TextEntry::make('subKegiatan.nama_sub_kegiatan')
                            ->label('Sub Kegiatan')
                            ->columnSpan(['default' => 2, 'sm' => 3, 'md' => 3]),

                        TextEntry::make('catatan_revisi')
                            ->label('Catatan Perbaikan / Revisi')
                            ->color('danger')
                            ->visible(fn(PengajuanSpj $record): bool => filled($record->catatan_revisi) && str_contains($record->status, 'revisi'))
                            ->columnSpanFull(),
                    ])
                    ->columns(['default' => 2, 'sm' => 3, 'md' => 6]),

                // Bagian Rincian Barang (Mandiri Di-scroll Vertikal dengan Max-Height)
                Section::make()
                    ->columnSpanFull()
                    ->compact()
                    ->extraAttributes([
                        'class' => 'spj-section-scroll',
                        'style' => 'max-height: 200px !important; overflow-y: auto !important; overflow-x: hidden !important;',
                    ])
                    ->schema([
                        RepeatableEntry::make('details')
                            ->hiddenLabel()
                            ->contained(false)
                            ->schema([
                                TextEntry::make('nama_barang')
                                    ->label('Uraian Barang')
                                    ->weight('bold')
                                    ->size(TextSize::Small)
                                    ->columnSpan(['default' => 12, 'md' => 4]),

                                TextEntry::make('keterangan')
                                    ->label('Keterangan')
                                    ->placeholder('-')
                                    ->size(TextSize::Small)
                                    ->columnSpan(['default' => 12, 'md' => 2]),

                                TextEntry::make('volume')
                                    ->label('Volume')
                                    ->size(TextSize::Small)
                                    ->state(fn(DetailPengajuanSpj $record): string => number_format((float) ($record->kuantitas_realisasi ?: $record->kuantitas), 0, ',', '.') . ' ' . $record->satuan_nama)
                                    ->columnSpan(['default' => 12, 'md' => 2]),

                                TextEntry::make('harga_satuan_item')
                                    ->label('Harga Satuan')
                                    ->size(TextSize::Small)
                                    ->state(fn(DetailPengajuanSpj $record): string => FormatHelper::rupiah((float) ($record->harga_realisasi ?: $record->harga_satuan)))
                                    ->columnSpan(['default' => 12, 'md' => 2]),

                                TextEntry::make('subtotal')
                                    ->label('Total Uang')
                                    ->weight('bold')
                                    ->color('warning')
                                    ->size(TextSize::Small)
                                    ->alignment(Alignment::End)
                                    ->state(fn(DetailPengajuanSpj $record): string => FormatHelper::rupiah((float) ($record->subtotal_realisasi ?: $record->subtotal)))
                                    ->columnSpan(['default' => 12, 'md' => 2]),
                            ])
                            ->columns(12),
                    ]),

                // Total belanja diletakkan di bawah rincian barang secara inline dan tetap terlihat
                TextEntry::make('total_belanja_bawah')
                    ->label('Total Belanja (Nilai Kwitansi)')
                    ->size(TextSize::Large)
                    ->weight('bold')
                    ->color('warning')
                    ->alignment(Alignment::End)
                    ->inlineLabel()
                    ->columnSpanFull()
                    ->state(function (PengajuanSpj $record): string {
                        $nominal = (float) ($record->total_nilai_realisasi ?: $record->total_nilai_rencana);

                        return $nominal > 0 ? FormatHelper::rupiah($nominal) : '-';
                    }),
            ]);
    }
}
