<?php

namespace App\Filament\Resources\PengajuanSpjs\Tables;

use App\Models\PengajuanSpj;
use App\Services\Spj\SpjWorkflowService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PengajuanSpjsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_pengajuan')
                    ->label('No. Pengajuan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('tanggal_pengajuan')
                    ->label('Tgl Pengajuan')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('kegiatan.nama_kegiatan')
                    ->label('Kegiatan')
                    ->searchable()
                    ->wrap()
                    ->limit(40),

                TextColumn::make('total_nilai_rencana')
                    ->label('Total Rencana')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('total_nilai_realisasi')
                    ->label('Total Realisasi')
                    ->money('IDR', locale: 'id')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status SPJ')
                    ->badge()
                    ->color(fn (PengajuanSpj $record): string => $record->status_color)
                    ->formatStateUsing(fn (PengajuanSpj $record): string => $record->status_label)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft Rencana',
                        'diajukan' => 'Diajukan ke PPTK',
                        'menunggu_realisasi' => 'Nota Pesanan Terbit / Belanja',
                        'menunggu_verifikasi_pptk' => 'Menunggu Verifikasi PPTK',
                        'revisi_pptk' => 'Revisi PPTK',
                        'menunggu_verifikasi_ppk' => 'Menunggu Verifikasi PPK',
                        'revisi_ppk' => 'Revisi PPK',
                        'menunggu_persetujuan_pa' => 'Menunggu Persetujuan PA',
                        'revisi_pa' => 'Revisi PA',
                        'menunggu_verifikasi_bendahara' => 'Menunggu Uji Kas Bendahara',
                        'revisi_bendahara' => 'Revisi Bendahara',
                        'disetujui' => 'Disetujui',
                        'dicairkan' => 'Dicairkan & Lunas',
                        'ditolak' => 'Ditolak Permanen',
                    ]),
                SelectFilter::make('bidang_id')
                    ->relationship('bidang', 'nama')
                    ->label('Bidang Dinas'),
            ])
            ->actions([
                // Aksi 1: Cetak Dokumen SPJ (Surat Pesanan & Kwitansi setelah di-TTD PPTK)
                Action::make('cetak_dokumen')
                    ->label(fn (PengajuanSpj $record): string => in_array($record->status, ['draft', 'diajukan'], true)
                        ? 'Cetak Surat Pesanan'
                        : 'Cetak Pesanan & Kwitansi')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (PengajuanSpj $record): string => route('admin.pengajuan-spj.cetak', $record))
                    ->openUrlInNewTab(),

                // Aksi 2: Verifikasi & Loloskan Tahapan (Sesuai State Machine)
                Action::make('proses_approval')
                    ->label(function (PengajuanSpj $record): string {
                        return match ($record->status) {
                            'draft' => 'Ajukan Surat Pesanan ke Kabid',
                            'diajukan' => 'TTD Surat Pesanan (Kabid/PPTK)',
                            'menunggu_realisasi' => 'Proses Surat Kwitansi',
                            'menunggu_verifikasi_pptk', 'revisi_pptk' => 'Verifikasi & TTD Kwitansi (PPTK)',
                            'menunggu_verifikasi_ppk', 'revisi_ppk' => 'Verifikasi Kwitansi (PPK)',
                            'menunggu_persetujuan_pa', 'revisi_pa' => 'TTD Kwitansi & NPD (PA/Kadis)',
                            'menunggu_verifikasi_bendahara', 'revisi_bendahara' => 'Cairkan & TTD Lunas Kwitansi',
                            default => 'Proses Tahapan',
                        };
                    })
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->visible(fn (PengajuanSpj $record): bool => !in_array($record->status, ['dicairkan', 'ditolak']))
                    ->form(function (PengajuanSpj $record): array {
                        $checklistOptions = match ($record->status) {
                            'menunggu_verifikasi_pptk' => [
                                'c1' => 'Kesesuaian barang dengan kebutuhan riil kegiatan',
                                'c2' => 'Kesesuaian volume kuantitas dan kewajaran harga',
                                'c3' => 'Kesesuaian barang fisik yang dibeli dengan nota pesanan',
                                'c4' => 'Kelengkapan lembar Nota Pesanan',
                                'c5' => 'Kelengkapan faktur / nota asli rekanan toko',
                                'c6' => 'Foto fisik dokumentasi barang (wajib jika > Rp 2 jt)',
                            ],
                            'menunggu_verifikasi_ppk' => [
                                'p1' => 'Kesesuaian pembebanan Program, Kegiatan, dan Subkegiatan',
                                'p2' => 'Ketepatan pembebanan Kode Rekening Belanja daerah',
                                'p3' => 'Ketersediaan sisa pagu anggaran dinas',
                                'p4' => 'Kesesuaian nominal pengajuan vs realisasi belanja',
                                'p5' => 'Pemeriksaan keabsahan dokumen dan perhitungan pajak (PPN/PPh)',
                            ],
                            'menunggu_persetujuan_pa' => [
                                'pa1' => 'Penelitian hasil verifikasi kepatuhan PPTK dan PPK',
                                'pa2' => 'Persetujuan nilai akhir pertanggungjawaban belanja',
                                'pa3' => 'Persetujuan Kwitansi dan Nota Pencairan Dana (NPD)',
                            ],
                            'menunggu_verifikasi_bendahara' => [
                                'b1' => 'Pemeriksaan final seluruh dokumen bukti fisik transaksi',
                                'b2' => 'Verifikasi pemotongan / pemungutan pajak sesuai regulasi',
                                'b3' => 'Kelengkapan seluruh tanda tangan pejabat (PPTK, PPK, PA, Rekanan)',
                            ],
                            default => [],
                        };

                        $schema = [];
                        if (!empty($checklistOptions)) {
                            $schema[] = CheckboxList::make('checklist')
                                ->label('Daftar Checklist Uji Kepatuhan (Wajib Centang Semua)')
                                ->options($checklistOptions)
                                ->required();
                        }

                        $schema[] = Textarea::make('catatan')
                            ->label('Catatan Persetujuan / Rekomendasi')
                            ->placeholder('Berkas lengkap dan memenuhi syarat.');

                        return $schema;
                    })
                    ->action(function (PengajuanSpj $record, array $data, SpjWorkflowService $workflowService): void {
                        $nextStatus = match ($record->status) {
                            'draft' => 'diajukan',
                            'diajukan' => 'menunggu_realisasi',
                            'menunggu_realisasi' => 'menunggu_verifikasi_pptk',
                            'menunggu_verifikasi_pptk' => 'menunggu_verifikasi_ppk',
                            'menunggu_verifikasi_ppk' => 'menunggu_persetujuan_pa',
                            'menunggu_persetujuan_pa' => 'menunggu_verifikasi_bendahara',
                            'menunggu_verifikasi_bendahara' => 'dicairkan',
                            default => $record->status,
                        };

                        $peran = match ($record->status) {
                            'draft' => 'staf',
                            'diajukan', 'menunggu_verifikasi_pptk' => 'pptk',
                            'menunggu_verifikasi_ppk' => 'ppk',
                            'menunggu_persetujuan_pa' => 'pa',
                            'menunggu_verifikasi_bendahara' => 'bendahara',
                            default => null,
                        };

                        $workflowService->transition(
                            $record,
                            $nextStatus,
                            $data['catatan'] ?? null,
                            $peran,
                            $data['checklist'] ?? null
                        );

                        Notification::make()
                            ->title('Status SPJ Berhasil Diperbarui')
                            ->body("Berkas kini berstatus: {$record->fresh()->status_label}")
                            ->success()
                            ->send();
                    }),

                // Aksi 3: Kembalikan untuk Revisi
                Action::make('kembalikan_revisi')
                    ->label('Revisi')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (PengajuanSpj $record): bool => in_array($record->status, [
                        'menunggu_verifikasi_pptk',
                        'menunggu_verifikasi_ppk',
                        'menunggu_persetujuan_pa',
                        'menunggu_verifikasi_bendahara',
                    ]))
                    ->form([
                        Textarea::make('catatan_revisi')
                            ->label('Catatan Instruksi Perbaikan / Revisi')
                            ->placeholder('Sebutkan item berkas atau nominal yang harus diperbaiki staf...')
                            ->required(),
                    ])
                    ->action(function (PengajuanSpj $record, array $data, SpjWorkflowService $workflowService): void {
                        $revisiStatus = match ($record->status) {
                            'menunggu_verifikasi_pptk' => 'revisi_pptk',
                            'menunggu_verifikasi_ppk' => 'revisi_ppk',
                            'menunggu_persetujuan_pa' => 'revisi_pa',
                            'menunggu_verifikasi_bendahara' => 'revisi_bendahara',
                            default => 'draft',
                        };

                        $workflowService->transition(
                            $record,
                            $revisiStatus,
                            $data['catatan_revisi'],
                            'pemeriksa'
                        );

                        Notification::make()
                            ->title('Pengajuan Dikembalikan untuk Revisi')
                            ->body('Catatan perbaikan telah dicatat dalam riwayat audit.')
                            ->warning()
                            ->send();
                    }),

                EditAction::make(),
                ViewAction::make(),
                DeleteAction::make()
                    ->visible(fn (PengajuanSpj $record): bool => in_array($record->status, ['draft', 'ditolak'])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

