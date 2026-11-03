<?php

namespace App\Filament\Resources\PengajuanSpjs\Tables;

use App\Models\Bidang;
use App\Models\PengajuanSpj;
use App\Services\Spj\SpjWorkflowService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PengajuanSpjsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                /** @var \App\Models\User|null $user */
                $user = Auth::user();
                $canViewAllBidang = $user !== null && $user->can('viewAny', Bidang::class);

                $anggaranRole = $user?->anggaran_role instanceof \App\Enums\AnggaranRole
                    ? $user->anggaran_role->value
                    : $user?->anggaran_role;

                if (! $canViewAllBidang && ! in_array($anggaranRole, ['PA', 'PPK', 'BENDAHARA'], true) && $user !== null) {
                    $userBidangId = filled($user->bidang_id)
                        ? (int) $user->bidang_id
                        : (int) (Bidang::where('kepala_id', $user->id)->value('id') ?? 0);

                    if ($userBidangId > 0) {
                        $query->where('bidang_id', $userBidangId);
                    }
                }

                return $query;
            })
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
                    ->label('Nilai Pesanan')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('total_nilai_realisasi')
                    ->label('Nilai Kwitansi')
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
                        'draft' => 'Draft Surat Pesanan',
                        'diajukan' => 'Menunggu TTD Surat Pesanan (Kabid/PPTK)',
                        'menunggu_realisasi' => 'Surat Pesanan Di-TTD / Proses Kwitansi',
                        'menunggu_verifikasi_pptk' => 'Kwitansi Menunggu Verifikasi & TTD PPTK',
                        'revisi_pptk' => 'Revisi PPTK',
                        'menunggu_verifikasi_ppk' => 'Kwitansi Menunggu Verifikasi PPK',
                        'revisi_ppk' => 'Revisi PPK',
                        'menunggu_persetujuan_pa' => 'Kwitansi Menunggu TTD PA (Kadis)',
                        'revisi_pa' => 'Revisi PA',
                        'menunggu_verifikasi_bendahara' => 'Kwitansi Menunggu Uji Kas Bendahara',
                        'revisi_bendahara' => 'Revisi Bendahara',
                        'disetujui' => 'Disetujui',
                        'dicairkan' => 'Kwitansi Lunas & Dicairkan',
                        'ditolak' => 'Ditolak Permanen',
                    ]),
                SelectFilter::make('bidang_id')
                    ->relationship('bidang', 'nama')
                    ->label('Bidang Dinas'),
            ])
            ->actions([
                // Aksi 1: Cetak Dokumen Bertahap (TIDAK TAMPIL saat masih Draft / Belum Disetujui Kabid)
                Action::make('cetak_dokumen')
                    ->label(function (PengajuanSpj $record): string {
                        return match ($record->status) {
                            'menunggu_realisasi' => 'Cetak Surat Pesanan',
                            'menunggu_verifikasi_pptk', 'revisi_pptk' => 'Cetak Pesanan & Kwitansi',
                            default => 'Cetak Pesanan, Kwitansi & NPD',
                        };
                    })
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->visible(fn (PengajuanSpj $record): bool => ! in_array($record->status, ['draft', 'diajukan', 'ditolak'], true))
                    ->url(fn (PengajuanSpj $record): string => route('admin.pengajuan-spj.cetak', $record))
                    ->openUrlInNewTab(),

                // Aksi 2: Proses Tahapan Secara Bertahap (Kontekstual sesuai Status)
                Action::make('proses_approval')
                    ->label(function (PengajuanSpj $record): string {
                        return match ($record->status) {
                            'draft' => 'Ajukan Surat Pesanan ke Kabid',
                            'diajukan' => 'Setujui & TTD Surat Pesanan',
                            'menunggu_realisasi', 'revisi_pptk' => 'Proses Surat Kwitansi',
                            'menunggu_verifikasi_pptk' => 'Verifikasi & TTD Kwitansi + NPD (PPTK)',
                            'menunggu_verifikasi_ppk', 'revisi_ppk' => 'Verifikasi Kwitansi & NPD (PPK)',
                            'menunggu_persetujuan_pa', 'revisi_pa' => 'Setujui & TTD Kwitansi + NPD (PA)',
                            'menunggu_verifikasi_bendahara', 'revisi_bendahara' => 'Cairkan & TTD Lunas Kwitansi',
                            default => 'Proses Tahapan',
                        };
                    })
                    ->icon(function (PengajuanSpj $record): string {
                        return match ($record->status) {
                            'draft' => 'heroicon-o-paper-airplane',
                            'diajukan' => 'heroicon-o-pencil-square',
                            'menunggu_realisasi', 'revisi_pptk' => 'heroicon-o-receipt-percent',
                            'menunggu_verifikasi_bendahara', 'revisi_bendahara' => 'heroicon-o-banknotes',
                            default => 'heroicon-o-check-circle',
                        };
                    })
                    ->color(function (PengajuanSpj $record): string {
                        return match ($record->status) {
                            'draft' => 'warning',
                            'diajukan' => 'info',
                            'menunggu_realisasi', 'revisi_pptk' => 'primary',
                            default => 'success',
                        };
                    })
                    ->visible(fn (PengajuanSpj $record): bool => ! in_array($record->status, ['disetujui', 'dicairkan', 'ditolak'], true))
                    ->form(function (PengajuanSpj $record): array {
                        $schema = [];

                        // Kasus 3: Saat Surat Pesanan sudah di-TTD Kabid, Staf memproses Surat Kwitansi & Upload Bukti
                        if (in_array($record->status, ['menunggu_realisasi', 'revisi_pptk'], true)) {
                            $nominalAktif = (float) ($record->total_nilai_realisasi ?: $record->total_nilai_rencana);
                            $wajibFotoBarang = $nominalAktif > 2000000;

                            $schema[] = TextInput::make('penerima_nama')
                                ->label('Nama Penerima Pembayaran pada Kwitansi')
                                ->default($record->penerima_nama ?: $record->nama_toko)
                                ->required();

                            $schema[] = TextInput::make('penerima_identitas')
                                ->label('NPWP / NIK / Jabatan Penerima Kwitansi')
                                ->default($record->penerima_identitas)
                                ->placeholder('Contoh: Pimpinan Toko / NPWP');

                            $schema[] = FileUpload::make('file_nota_penjual')
                                ->label('Unggah Faktur / Nota Asli Penjual (Wajib untuk Proses Kwitansi)')
                                ->directory('spj/nota-penjual')
                                ->default($record->file_nota_penjual)
                                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                ->maxSize(5120)
                                ->required(fn () => empty($record->file_nota_penjual));

                            $schema[] = FileUpload::make('file_dokumentasi_barang')
                                ->label($wajibFotoBarang
                                    ? 'Unggah Foto Dokumentasi Fisik Barang (WAJIB karena nilai > Rp 2.000.000)'
                                    : 'Unggah Foto Dokumentasi Fisik Barang (Opsional)')
                                ->directory('spj/dokumentasi-barang')
                                ->default($record->file_dokumentasi_barang)
                                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                ->maxSize(5120)
                                ->required(fn () => $wajibFotoBarang && empty($record->file_dokumentasi_barang));

                            if ((float) $record->total_nilai_realisasi > 0 && (float) $record->total_nilai_realisasi !== (float) $record->total_nilai_rencana) {
                                $schema[] = Textarea::make('justifikasi_selisih')
                                    ->label('Justifikasi Selisih Nilai Pesanan vs Kwitansi (Wajib)')
                                    ->default($record->justifikasi_selisih)
                                    ->required();
                            }
                        }

                        // Kasus 4: Checklist Pejabat Pemeriksa sesuai tahapan aktif
                        $checklistOptions = match ($record->status) {
                            'diajukan' => [
                                'sp1' => 'Kesesuaian jenis barang dengan kebutuhan kegiatan bidang',
                                'sp2' => 'Ketersediaan stok barang dan kewajaran volume pesanan',
                                'sp3' => 'Persetujuan penandatanganan Surat Pesanan / Nota Pesanan',
                            ],
                            'menunggu_verifikasi_pptk' => [
                                'c1' => 'Kesesuaian barang dengan kebutuhan riil kegiatan',
                                'c2' => 'Kesesuaian volume kuantitas dan kewajaran harga',
                                'c3' => 'Kesesuaian barang fisik yang dibeli dengan Surat Pesanan',
                                'c4' => 'Kelengkapan lembar Surat Pesanan yang telah di-TTD',
                                'c5' => 'Kelengkapan faktur / nota asli rekanan toko',
                                'c6' => 'Foto fisik dokumentasi barang (wajib jika > Rp 2 jt)',
                            ],
                            'menunggu_verifikasi_ppk', 'revisi_ppk' => [
                                'p1' => 'Kesesuaian pembebanan Program, Kegiatan, dan Subkegiatan',
                                'p2' => 'Ketepatan pembebanan Kode Rekening Belanja daerah',
                                'p3' => 'Ketersediaan sisa pagu anggaran dinas',
                                'p4' => 'Kesesuaian nominal Surat Pesanan vs Surat Kwitansi',
                                'p5' => 'Pemeriksaan keabsahan dokumen dan perhitungan pajak (PPN/PPh)',
                            ],
                            'menunggu_persetujuan_pa', 'revisi_pa' => [
                                'pa1' => 'Penelitian hasil verifikasi kepatuhan PPTK dan PPK',
                                'pa2' => 'Persetujuan nilai akhir pertanggungjawaban belanja',
                                'pa3' => 'Persetujuan dan TTD Surat Kwitansi & Nota Pencairan Dana (NPD)',
                            ],
                            'menunggu_verifikasi_bendahara', 'revisi_bendahara' => [
                                'b1' => 'Pemeriksaan final seluruh dokumen bukti fisik transaksi',
                                'b2' => 'Verifikasi pemotongan / pemungutan pajak sesuai regulasi',
                                'b3' => 'Kelengkapan seluruh tanda tangan pejabat (PPTK, PPK, PA, Rekanan)',
                            ],
                            default => [],
                        };

                        if (! empty($checklistOptions)) {
                            $schema[] = CheckboxList::make('checklist')
                                ->label('Daftar Checklist Uji Kepatuhan Tahap Ini (Wajib Centang)')
                                ->options($checklistOptions)
                                ->required();
                        }

                        // Upload Spesimen TTD Pejabat (Non-TTE) sesuai peran yang sedang memproses
                        if (in_array($record->status, ['diajukan', 'menunggu_verifikasi_pptk'], true)) {
                            $schema[] = FileUpload::make('ttd_pptk')
                                ->label('Unggah Spesimen TTD PPTK / Kepala Bidang (PNG/JPG - Opsional)')
                                ->directory('spj/ttd')
                                ->default($record->ttd_pptk)
                                ->image()
                                ->maxSize(2048);
                        } elseif (in_array($record->status, ['menunggu_verifikasi_ppk', 'revisi_ppk'], true)) {
                            $schema[] = FileUpload::make('ttd_ppk')
                                ->label('Unggah Spesimen TTD PPK / Kasubag Keuangan (PNG/JPG - Opsional)')
                                ->directory('spj/ttd')
                                ->default($record->ttd_ppk)
                                ->image()
                                ->maxSize(2048);
                        } elseif (in_array($record->status, ['menunggu_persetujuan_pa', 'revisi_pa'], true)) {
                            $schema[] = FileUpload::make('ttd_pa')
                                ->label('Unggah Spesimen TTD PA / KPA / Kepala Dinas (PNG/JPG - Opsional)')
                                ->directory('spj/ttd')
                                ->default($record->ttd_pa)
                                ->image()
                                ->maxSize(2048);
                        } elseif (in_array($record->status, ['menunggu_verifikasi_bendahara', 'revisi_bendahara'], true)) {
                            $schema[] = FileUpload::make('ttd_bendahara')
                                ->label('Unggah Spesimen TTD Bendahara Pengeluaran (PNG/JPG - Opsional)')
                                ->directory('spj/ttd')
                                ->default($record->ttd_bendahara)
                                ->image()
                                ->maxSize(2048);
                        }

                        // Kasus 5: Saat Bendahara mencairkan dana, wajib isi Nomor BKU & Tanggal Cair
                        if (in_array($record->status, ['menunggu_verifikasi_bendahara', 'revisi_bendahara'], true)) {
                            $schema[] = TextInput::make('nomor_bku')
                                ->label('Nomor Buku Kas Umum (BKU)')
                                ->default($record->nomor_bku)
                                ->placeholder('Contoh: BKU/2026/11/0045')
                                ->required();

                            $schema[] = DatePicker::make('tanggal_dicairkan')
                                ->label('Tanggal Pencairan Kas')
                                ->default($record->tanggal_dicairkan ?? now())
                                ->displayFormat('d/m/Y')
                                ->required();
                        }

                        $schema[] = Textarea::make('catatan')
                            ->label('Catatan Tindakan / Keterangan')
                            ->placeholder('Tambahkan catatan pemeriksaan atau keterangan tahapan ini...');

                        return $schema;
                    })
                    ->action(function (PengajuanSpj $record, array $data, SpjWorkflowService $workflowService): void {
                        // Simpan data tambahan pada tahapan terkait sebelum transisi status
                        $extraUpdates = [];
                        foreach (['penerima_nama', 'penerima_identitas', 'file_nota_penjual', 'file_dokumentasi_barang', 'justifikasi_selisih', 'nomor_bku', 'tanggal_dicairkan', 'ttd_pptk', 'ttd_ppk', 'ttd_pa', 'ttd_bendahara'] as $field) {
                            if (! empty($data[$field])) {
                                $extraUpdates[$field] = $data[$field];
                            }
                        }
                        if (! empty($extraUpdates)) {
                            $record->update($extraUpdates);
                        }

                        $nextStatus = match ($record->status) {
                            'draft' => 'diajukan',
                            'diajukan' => 'menunggu_realisasi',
                            'menunggu_realisasi', 'revisi_pptk' => 'menunggu_verifikasi_pptk',
                            'menunggu_verifikasi_pptk' => 'menunggu_verifikasi_ppk',
                            'menunggu_verifikasi_ppk', 'revisi_ppk' => 'menunggu_persetujuan_pa',
                            'menunggu_persetujuan_pa', 'revisi_pa' => 'menunggu_verifikasi_bendahara',
                            'menunggu_verifikasi_bendahara', 'revisi_bendahara' => 'dicairkan',
                            default => $record->status,
                        };

                        $peran = match ($record->status) {
                            'draft', 'menunggu_realisasi', 'revisi_pptk' => 'staf',
                            'diajukan', 'menunggu_verifikasi_pptk' => 'pptk',
                            'menunggu_verifikasi_ppk', 'revisi_ppk' => 'ppk',
                            'menunggu_persetujuan_pa', 'revisi_pa' => 'pa',
                            'menunggu_verifikasi_bendahara', 'revisi_bendahara' => 'bendahara',
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
                            ->title('Tahapan Berhasil Diproses')
                            ->body("Status berkas saat ini: {$record->fresh()->status_label}")
                            ->success()
                            ->send();
                    }),

                // Aksi 3: Kembalikan untuk Revisi (Hanya muncul saat di meja pemeriksa)
                Action::make('kembalikan_revisi')
                    ->label(fn (PengajuanSpj $record): string => $record->status === 'diajukan' ? 'Revisi Pesanan' : 'Kembalikan Revisi')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (PengajuanSpj $record): bool => in_array($record->status, [
                        'diajukan',
                        'menunggu_verifikasi_pptk',
                        'menunggu_verifikasi_ppk',
                        'menunggu_persetujuan_pa',
                        'menunggu_verifikasi_bendahara',
                    ], true))
                    ->form([
                        Textarea::make('catatan_revisi')
                            ->label('Catatan Instruksi Perbaikan / Revisi')
                            ->placeholder('Sebutkan item pesanan, berkas kwitansi, atau nominal yang harus diperbaiki...')
                            ->required(),
                    ])
                    ->action(function (PengajuanSpj $record, array $data, SpjWorkflowService $workflowService): void {
                        $revisiStatus = match ($record->status) {
                            'diajukan' => 'draft',
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
                            ->title('Berkas Dikembalikan untuk Revisi')
                            ->body('Catatan perbaikan telah disimpan dalam riwayat audit.')
                            ->warning()
                            ->send();
                    }),

                // Edit hanya tampil jika berkas belum terkunci (disetujui/dicairkan/ditolak)
                EditAction::make()
                    ->visible(fn (PengajuanSpj $record): bool => ! in_array($record->status, ['disetujui', 'dicairkan', 'ditolak'], true)),

                ViewAction::make(),

                // Hapus hanya tampil jika masih berstatus draft awal
                DeleteAction::make()
                    ->visible(fn (PengajuanSpj $record): bool => $record->status === 'draft'),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Buat Pengajuan SPJ')
                    ->icon('heroicon-m-plus'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
