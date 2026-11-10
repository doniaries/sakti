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

class PengajuanSpjInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengajuan')
                    ->schema([
                        TextEntry::make('nomor_pengajuan')
                            ->label('No. Pengajuan')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('status')
                            ->label('Status SPJ')
                            ->badge()
                            ->color(fn (PengajuanSpj $record): string => $record->status_color)
                            ->formatStateUsing(fn (PengajuanSpj $record): string => $record->status_label),

                        TextEntry::make('nilai_kwitansi')
                            ->label('Nilai Kwitansi')
                            ->weight('bold')
                            ->color('primary')
                            ->state(function (PengajuanSpj $record): string {
                                $nominal = (float) ($record->total_nilai_realisasi ?: $record->total_nilai_rencana);

                                return $nominal > 0 ? FormatHelper::rupiah($nominal) : '-';
                            }),

                        TextEntry::make('user.name')
                            ->label('Pemohon')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('bidang.nama')
                            ->label('Bidang')
                            ->badge()
                            ->color(fn (?string $state): string => Bidang::badgeColor($state)),

                        TextEntry::make('tanggal_pengajuan')
                            ->label('Tgl Pengajuan')
                            ->date('d/m/Y'),

                        TextEntry::make('nama_penyedia')
                            ->label('Penyedia')
                            ->badge()
                            ->color('success')
                            ->state(fn (PengajuanSpj $record): string => $record->nama_toko ?: ($record->masterToko?->nama_toko ?: '-')),

                        TextEntry::make('kegiatan.nama_kegiatan')
                            ->label('Kegiatan')
                            ->columnSpan(2),

                        TextEntry::make('subKegiatan.nama_sub_kegiatan')
                            ->label('Sub Kegiatan')
                            ->columnSpanFull(),

                        TextEntry::make('catatan_revisi')
                            ->label('Catatan Perbaikan / Revisi')
                            ->color('danger')
                            ->visible(fn (PengajuanSpj $record): bool => filled($record->catatan_revisi) && str_contains($record->status, 'revisi'))
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Rincian Barang & Volume')
                    ->schema([
                        RepeatableEntry::make('details')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('nama_barang')
                                    ->label('Uraian Barang')
                                    ->weight('bold')
                                    ->columnSpan(4),

                                TextEntry::make('keterangan')
                                    ->label('Keterangan')
                                    ->placeholder('-')
                                    ->columnSpan(3),

                                TextEntry::make('volume')
                                    ->label('Volume')
                                    ->state(fn (DetailPengajuanSpj $record): string => number_format((float) ($record->kuantitas_realisasi ?: $record->kuantitas), 0, ',', '.') . ' ' . $record->satuan_nama)
                                    ->columnSpan(2),

                                TextEntry::make('harga_satuan_item')
                                    ->label('Harga Satuan')
                                    ->state(fn (DetailPengajuanSpj $record): string => FormatHelper::rupiah((float) ($record->harga_realisasi ?: $record->harga_satuan)))
                                    ->columnSpan(3),
                            ])
                            ->columns(12),
                    ]),
            ]);
    }
}
