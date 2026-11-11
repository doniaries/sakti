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

                // Bagian Rincian Barang (Tabel Ramping, Header Sticky, Body Scrollable Mandiri)
                TextEntry::make('rincian_barang_table')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->html()
                    ->state(function (PengajuanSpj $record): string {
                        $details = $record->details;
                        if ($details->isEmpty()) {
                            return '<div class="rounded-lg border border-gray-200 p-4 text-center text-xs italic text-gray-500 dark:border-gray-700">Tidak ada rincian barang.</div>';
                        }

                        $rows = '';
                        foreach ($details as $index => $item) {
                            $no = $index + 1;
                            $nama = htmlspecialchars($item->nama_barang ?? '-', ENT_QUOTES, 'UTF-8');
                            $ket = htmlspecialchars($item->keterangan ?: '-', ENT_QUOTES, 'UTF-8');
                            $vol = number_format((float) ($item->kuantitas_realisasi ?: $item->kuantitas), 0, ',', '.') . ' ' . htmlspecialchars($item->satuan_nama ?? '', ENT_QUOTES, 'UTF-8');
                            $harga = FormatHelper::rupiah((float) ($item->harga_realisasi ?: $item->harga_satuan));
                            $subtotal = FormatHelper::rupiah((float) ($item->subtotal_realisasi ?: $item->subtotal));

                            $rows .= "
                                <tr class='border-b border-gray-100 hover:bg-gray-50/80 dark:border-gray-800 dark:hover:bg-white/5 transition-colors'>
                                    <td class='px-3 py-1.5 text-center text-xs text-gray-500 font-medium'>{$no}</td>
                                    <td class='px-3 py-1.5 text-xs font-semibold text-gray-900 dark:text-gray-100'>{$nama}</td>
                                    <td class='px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400'>{$ket}</td>
                                    <td class='px-3 py-1.5 text-center text-xs font-medium text-gray-700 dark:text-gray-300'>{$vol}</td>
                                    <td class='px-3 py-1.5 text-right text-xs text-gray-700 dark:text-gray-300'>{$harga}</td>
                                    <td class='px-3 py-1.5 text-right text-xs font-bold text-amber-600 dark:text-amber-400'>{$subtotal}</td>
                                </tr>
                            ";
                        }

                        return "
                            <div class='rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-900 shadow-2xs'>
                                <div class='overflow-y-auto' style='max-height: 180px; overflow-y: auto; display: block;'>
                                    <table class='w-full text-left border-collapse'>
                                        <thead class='bg-gray-100/90 dark:bg-gray-800/90 sticky top-0 z-10 border-b border-gray-200 dark:border-gray-700 text-[11px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 shadow-2xs'>
                                            <tr>
                                                <th class='px-3 py-2 text-center w-10'>No</th>
                                                <th class='px-3 py-2'>Uraian Barang</th>
                                                <th class='px-3 py-2'>Keterangan</th>
                                                <th class='px-3 py-2 text-center w-24'>Volume</th>
                                                <th class='px-3 py-2 text-right w-28'>Harga Satuan</th>
                                                <th class='px-3 py-2 text-right w-32'>Total Uang</th>
                                            </tr>
                                        </thead>
                                        <tbody class='divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900'>
                                            {$rows}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        ";
                    }),

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
