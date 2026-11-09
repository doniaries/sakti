<?php

namespace App\Filament\Resources\PengajuanSpjs\Tables;

use App\Models\Bidang;
use App\Models\PengajuanSpj;
use App\Services\Spj\SpjWorkflowService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PengajuanSpjsTable
{
    /**
     * Resolusi icon adaptif: menggunakan FontAwesome jika package terinstal di vendor server,
     * dengan graceful fallback ke Heroicons bawaan Filament jika composer install belum dijalankan di server.
     */
    protected static function safeIcon(string $faIcon, string $heroicon): string
    {
        static $hasFontAwesome = null;
        $hasFontAwesome ??= class_exists(\OwenVoke\BladeFontAwesome\BladeFontAwesomeServiceProvider::class);

        return $hasFontAwesome ? $faIcon : $heroicon;
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query, Table $table): Builder {
                $query->with([
                    'bidang',
                    'kegiatan.program',
                    'subKegiatan',
                    'user',
                    'masterToko',
                    'pptk',
                ]);

                /** @var \App\Models\User|null $user */
                $user = Auth::user();
                if ($user && ! $user->canViewAllBidangKeuangan()) {
                    $userBidangId = (int) ($user->bidang_id ?: (Bidang::where('kepala_id', $user->id)->value('id') ?? 0));
                    $query->where(function (Builder $q) use ($user, $userBidangId) {
                        if ($userBidangId > 0) {
                            $q->where('bidang_id', $userBidangId);
                        }
                        $q->orWhere('user_id', $user->id);
                    });
                }

                // Sinkronisasi filter bidang dari parameter URL / Tab Widget Bidang (sesuai Rule 8 AGENTS.md)
                $livewire = $table->getLivewire();
                $livewireFilters = property_exists($livewire, 'tableFilters') ? $livewire->tableFilters : null;

                $selectedBidang = request()->query('bidang')
                    ?? data_get(request()->query('filters'), 'bidang_id.value')
                    ?? data_get(request()->query('tableFilters'), 'bidang_id.value')
                    ?? data_get($livewireFilters, 'bidang_id.value');

                $selectedTahun = request()->query('tahun')
                    ?? data_get(request()->query('filters'), 'tahun_anggaran.value')
                    ?? data_get(request()->query('tableFilters'), 'tahun_anggaran.value')
                    ?? data_get($livewireFilters, 'tahun_anggaran.value');

                if ((blank($selectedBidang) || blank($selectedTahun)) && request()->header('Referer')) {
                    $refererQuery = [];
                    parse_str(parse_url(request()->header('Referer'), PHP_URL_QUERY) ?? '', $refererQuery);
                    if (blank($selectedBidang)) {
                        $selectedBidang = $refererQuery['bidang']
                            ?? data_get($refererQuery, 'filters.bidang_id.value')
                            ?? data_get($refererQuery, 'tableFilters.bidang_id.value');
                    }
                    if (blank($selectedTahun)) {
                        $selectedTahun = $refererQuery['tahun']
                            ?? data_get($refererQuery, 'filters.tahun_anggaran.value')
                            ?? data_get($refererQuery, 'tableFilters.tahun_anggaran.value');
                    }
                }

                $tahunAktif = (int) ($selectedTahun ?: date('Y'));

                if (filled($selectedBidang)) {
                    $query->where('bidang_id', (int) $selectedBidang);
                }

                $query->where('tahun_anggaran', $tahunAktif);

                return $query;
            })
            ->recordActionsPosition(RecordActionsPosition::BeforeColumns)
            ->recordActionsColumnLabel('Aksi')
            ->stickyableColumns()
            ->columns([
                TextColumn::make('nomor_pengajuan')
                    ->label('No. Pengajuan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Nomor pengajuan berhasil disalin')
                    ->weight('bold')
                    ->sticky(),

                TextColumn::make('status')
                    ->label('Status SPJ')
                    ->badge()
                    ->color(fn(PengajuanSpj $record): string => $record->status_color)
                    ->formatStateUsing(fn(PengajuanSpj $record): string => $record->status_label)
                    ->sortable(),

                TextColumn::make('total_nilai_realisasi')
                    ->label('Nilai Kwitansi')
                    ->weight('bold')
                    ->formatStateUsing(function (PengajuanSpj $record): string {
                        $nominal = (float) ($record->total_nilai_realisasi ?: $record->total_nilai_rencana);
                        return $nominal > 0 ? 'Rp ' . number_format($nominal, 0, ',', '.') : '-';
                    })
                    ->sortable(),

                TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->badge()
                    ->color(fn(?string $state): string => Bidang::badgeColor($state))
                    ->searchable()
                    ->sortable()
                    ->stickyable(),

                TextColumn::make('metode_pengadaan')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'e_katalog' => 'E-Katalog',
                        default => 'Non-Katalog',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'e_katalog' => 'info',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('jenis_belanja')
                    ->label('Jenis Belanja')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'barang_jasa' => 'Barang & Jasa',
                        'makan_minum' => 'Makan Minum',
                        'sppd' => 'SPPD',
                        'bbm' => 'BBM',
                        default => 'Barang & Jasa',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'barang_jasa' => 'primary',
                        'makan_minum' => 'warning',
                        'sppd' => 'success',
                        'bbm' => 'danger',
                        default => 'primary',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('kegiatan.nama_kegiatan')
                    ->label('Kegiatan')
                    ->searchable()
                    ->wrap()
                    ->limit(40),

                TextColumn::make('tanggal_pengajuan')
                    ->label('Tgl Pengajuan')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('tahun_anggaran')
                    ->label('Tahun')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('tahun_anggaran')
                    ->label('Pilih Tahun')
                    ->options([
                        2024 => 'Tahun 2024',
                        2025 => 'Tahun 2025',
                        2026 => 'Tahun 2026',
                        2027 => 'Tahun 2027',
                        2028 => 'Tahun 2028',
                    ])
                    ->placeholder('Semua Tahun'),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft Surat Pesanan',
                        'diajukan' => 'Surat Pesanan Menunggu TTD (PPTK)',
                        'menunggu_realisasi' => 'Pesanan Disetujui',
                        'menunggu_verifikasi_pptk' => 'SPJ Menunggu Verifikasi (PPTK)',
                        'revisi_pptk' => 'SPJ Perlu Revisi (PPTK)',
                        'menunggu_verifikasi_ppk' => 'SPJ Menunggu Verifikasi (PPK)',
                        'revisi_ppk' => 'SPJ Perlu Revisi (PPK)',
                        'menunggu_persetujuan_pa' => 'SPJ Menunggu Persetujuan (PA)',
                        'revisi_pa' => 'SPJ Perlu Revisi (PA)',
                        'menunggu_verifikasi_bendahara' => 'SPJ Menunggu Uji Kas (Bendahara)',
                        'revisi_bendahara' => 'SPJ Perlu Revisi (Bendahara)',
                        'disetujui' => 'SPJ Disetujui (Siap Dicairkan)',
                        'dicairkan' => 'SPJ Lunas & Selesai Dicairkan',
                        'ditolak' => 'SPJ Ditolak Permanen',
                    ]),
                SelectFilter::make('bidang_id')
                    ->relationship('bidang', 'nama')
                    ->label('Bidang Dinas'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                // Aksi 1: Preview & Cetak Dokumen Bertahap dengan Modal Preview Interaktif (Icon Button dengan Tooltip)
                Action::make('cetak_dokumen')
                    ->label(fn(PengajuanSpj $record): string => match ($record->status) {
                        'menunggu_realisasi' => 'Cetak Pesanan',
                        default => 'Cek SPJ',
                    })
                    ->tooltip(fn(PengajuanSpj $record): string => match ($record->status) {
                        'menunggu_realisasi' => 'Cetak Surat Pesanan Barang (F4)',
                        default => 'Cek & Pratinjau Dokumen SPJ Lengkap',
                    })
                    ->icon(fn(PengajuanSpj $record): string => static::safeIcon(
                        $record->status === 'menunggu_realisasi' ? 'fas-print' : 'fas-file-lines',
                        'heroicon-o-document-magnifying-glass'
                    ))
                    ->iconButton()
                    ->color('info')
                    ->visible(fn(PengajuanSpj $record): bool => ! in_array($record->status, ['draft', 'diajukan', 'ditolak'], true))
                    ->modalHeading(fn(PengajuanSpj $record): string => "Pratinjau Dokumen SPJ — {$record->nomor_pengajuan}")
                    ->modalWidth(\Filament\Support\Enums\Width::SevenExtraLarge)
                    ->modalAutofocus(false)
                    ->modalContent(fn(PengajuanSpj $record) => view('filament.pages.preview-spj-modal', [
                        'record' => $record,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                // Aksi 2.A: Ajukan Pesanan Langsung (Konfirmasi Cepat 1-Klik tanpa Modal Form Simpan)
                Action::make('ajukan_pesanan')
                    ->label('Ajukan Pesanan')
                    ->tooltip('Ajukan Pesanan: Teruskan draf ke PPTK / Kabid')
                    ->icon(static::safeIcon('fas-paper-plane', 'heroicon-o-paper-airplane'))
                    ->iconButton()
                    ->color('warning')
                    ->visible(function (PengajuanSpj $record): bool {
                        if ($record->status !== 'draft') {
                            return false;
                        }

                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        if (! $user) {
                            return false;
                        }

                        return $user->hasAnyRole(['super_admin', 'admin', 'jf', 'pegawai']) || $user->id === $record->user_id;
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Ajukan Surat Pesanan ke PPTK?')
                    ->modalDescription(fn(PengajuanSpj $record): string => "Surat Pesanan {$record->nomor_pengajuan} senilai Rp " . number_format((float) $record->total_nilai_rencana, 0, ',', '.') . " akan langsung diajukan ke PPTK untuk diteliti dan disetujui. Pastikan rincian pesanan sudah sesuai.")
                    ->modalSubmitActionLabel('Ya, Ajukan Pesanan')
                    ->modalCancelActionLabel('Batal')
                    ->action(function (PengajuanSpj $record, SpjWorkflowService $workflowService): void {
                        $workflowService->transition(
                            $record,
                            'diajukan',
                            'Surat pesanan diajukan oleh pemohon ke PPTK.',
                            'staf'
                        );

                        Notification::make()
                            ->title('Surat Pesanan Berhasil Diajukan')
                            ->body("Surat Pesanan {$record->nomor_pengajuan} berhasil diteruskan ke PPTK.")
                            ->success()
                            ->send();
                    }),

                // Aksi 2.B: Batalkan Pengajuan (Tarik Kembali ke Status Draft jika belum disetujui PPTK)
                Action::make('batalkan_pengajuan')
                    ->label('Tarik ke Draft')
                    ->tooltip('Tarik ke Draft: Batalkan pengajuan dan kembalikan ke status draf')
                    ->icon(static::safeIcon('fas-rotate-left', 'heroicon-o-arrow-uturn-left'))
                    ->iconButton()
                    ->color('gray')
                    ->visible(function (PengajuanSpj $record): bool {
                        if ($record->status !== 'diajukan') {
                            return false;
                        }

                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        if (! $user) {
                            return false;
                        }

                        return $user->hasAnyRole(['super_admin', 'admin']) || $user->id === $record->user_id;
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Pengajuan Surat Pesanan?')
                    ->modalDescription(fn(PengajuanSpj $record): string => "Apakah Anda yakin ingin membatalkan pengajuan Surat Pesanan {$record->nomor_pengajuan}? Berkas akan dikembalikan ke status Draft sehingga dapat Anda perbaiki kembali.")
                    ->modalSubmitActionLabel('Ya, Tarik ke Draft')
                    ->modalCancelActionLabel('Batal')
                    ->action(function (PengajuanSpj $record, SpjWorkflowService $workflowService): void {
                        $workflowService->transition(
                            $record,
                            'draft',
                            'Pengajuan ditarik kembali oleh pemohon ke status draft.',
                            'staf'
                        );

                        Notification::make()
                            ->title('Pengajuan Berhasil Ditarik')
                            ->body("Surat Pesanan {$record->nomor_pengajuan} telah dikembalikan ke status Draft.")
                            ->info()
                            ->send();
                    }),

                // Aksi 2.C: Proses Tahapan Secara Bertahap (Kontekstual sesuai Status SPJ)
                Action::make('proses_approval')
                    ->label(function (PengajuanSpj $record): string {
                        return match ($record->status) {
                            'diajukan' => 'Cek Dokumen',
                            'menunggu_realisasi', 'revisi_pptk' => 'Lengkapi SPJ',
                            'menunggu_verifikasi_pptk' => 'Verifikasi PPTK',
                            'menunggu_verifikasi_ppk', 'revisi_ppk' => 'Verifikasi PPK',
                            'menunggu_persetujuan_pa', 'revisi_pa' => 'Pengesahan PA',
                            'menunggu_verifikasi_bendahara', 'revisi_bendahara' => 'Uji Kas & Cairkan',
                            default => 'Proses SPJ',
                        };
                    })
                    ->tooltip(function (PengajuanSpj $record): string {
                        return match ($record->status) {
                            'diajukan' => 'Cek Dokumen: Periksa dan verifikasi kelengkapan pesanan SPJ',
                            'menunggu_realisasi', 'revisi_pptk' => 'Lengkapi SPJ: Unggah faktur toko, dokumentasi barang, dan kelengkapan berkas',
                            'menunggu_verifikasi_pptk' => 'Verifikasi PPTK: Pemeriksaan kepatuhan berkas SPJ lengkap',
                            'menunggu_verifikasi_ppk', 'revisi_ppk' => 'Verifikasi PPK: Uji berkas SPJ, kode rekening, dan perpajakan',
                            'menunggu_persetujuan_pa', 'revisi_pa' => 'Pengesahan PA: Persetujuan akhir & pengesahan SPJ oleh Kepala Dinas',
                            'menunggu_verifikasi_bendahara', 'revisi_bendahara' => 'Uji Kas & Cairkan: Pemeriksaan kas & pencairan dana oleh Bendahara',
                            default => 'Proses tahapan berkas SPJ ini',
                        };
                    })
                    ->modalHeading(fn(PengajuanSpj $record): string => match ($record->status) {
                        'diajukan' => 'Pemeriksaan Dokumen & Persetujuan Pesanan (PPTK)',
                        'menunggu_realisasi', 'revisi_pptk' => 'Proses Kelengkapan Berkas SPJ',
                        'menunggu_verifikasi_pptk' => 'Verifikasi Berkas Dokumen SPJ (PPTK)',
                        'menunggu_verifikasi_ppk', 'revisi_ppk' => 'Verifikasi Berkas Dokumen SPJ (PPK)',
                        'menunggu_persetujuan_pa', 'revisi_pa' => 'Persetujuan & Pengesahan SPJ (PA / Kepala Dinas)',
                        'menunggu_verifikasi_bendahara', 'revisi_bendahara' => 'Uji Kas & Pencairan Dokumen SPJ (Bendahara)',
                        default => 'Pemeriksaan Dokumen SPJ',
                    })
                    ->icon(function (PengajuanSpj $record): string {
                        return static::safeIcon(
                            match ($record->status) {
                                'diajukan' => 'fas-file-signature',
                                'menunggu_realisasi', 'revisi_pptk' => 'fas-receipt',
                                'menunggu_verifikasi_pptk' => 'fas-list-check',
                                'menunggu_verifikasi_ppk', 'revisi_ppk' => 'fas-stamp',
                                'menunggu_persetujuan_pa', 'revisi_pa' => 'fas-signature',
                                'menunggu_verifikasi_bendahara', 'revisi_bendahara' => 'fas-money-bill-wave',
                                default => 'fas-circle-check',
                            },
                            match ($record->status) {
                                'diajukan' => 'heroicon-o-pencil-square',
                                'menunggu_realisasi', 'revisi_pptk' => 'heroicon-o-receipt-percent',
                                'menunggu_verifikasi_bendahara', 'revisi_bendahara' => 'heroicon-o-banknotes',
                                default => 'heroicon-o-check-circle',
                            }
                        );
                    })
                    ->iconButton()
                    ->color(function (PengajuanSpj $record): string {
                        return match ($record->status) {
                            'diajukan' => 'info',
                            'menunggu_realisasi', 'revisi_pptk' => 'primary',
                            default => 'success',
                        };
                    })
                    ->modalWidth(\Filament\Support\Enums\Width::SevenExtraLarge)
                    ->modalAutofocus(false)
                    ->visible(function (PengajuanSpj $record): bool {
                        if (in_array($record->status, ['draft', 'disetujui', 'dicairkan', 'ditolak'], true)) {
                            return false;
                        }

                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        if (! $user) {
                            return false;
                        }

                        if ($user->hasAnyRole(['super_admin', 'admin'])) {
                            return true;
                        }

                        $anggaranRole = $user->anggaran_role instanceof \App\Enums\AnggaranRole
                            ? $user->anggaran_role->value
                            : $user->anggaran_role;

                        $isPptkThisRecord = ($user->hasRole('pptk') || $anggaranRole === 'PPTK')
                            && ($user->id === $record->pptk_user_id || ((int) $user->bidang_id > 0 && (int) $user->bidang_id === (int) $record->bidang_id) || (int) $record->bidang?->kepala_id === (int) $user->id || $user->hasRole('super_admin'));

                        return match ($record->status) {
                            'diajukan' => $isPptkThisRecord,
                            'menunggu_realisasi', 'revisi_pptk' => $user->hasAnyRole(['jf', 'pegawai']) || $user->id === $record->user_id,
                            'menunggu_verifikasi_pptk' => $isPptkThisRecord,
                            'menunggu_verifikasi_ppk', 'revisi_ppk' => $user->hasRole('ppk') || $anggaranRole === 'PPK' || $user->id === $record->ppk_user_id,
                            'menunggu_persetujuan_pa', 'revisi_pa' => $user->hasRole('pa') || $anggaranRole === 'PA' || $user->id === $record->pa_user_id,
                            'menunggu_verifikasi_bendahara', 'revisi_bendahara' => $user->hasRole('bendahara') || $anggaranRole === 'BENDAHARA' || $user->id === $record->bendahara_user_id,
                            default => true,
                        };
                    })
                    ->form(function (PengajuanSpj $record): array {
                        $schema = [];

                        // Kasus 3: Saat Surat Pesanan sudah di-TTD Kabid, Staf memproses Surat Kwitansi & Upload Bukti Syarat Dokumen
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

                            // Ambil seluruh syarat dokumen resmi dari MasterSyaratBelanja untuk transaksi ini
                            $syaratBelanjaList = \App\Models\MasterSyaratBelanja::query()
                                ->where('aktif', true)
                                ->where(function ($q) use ($record) {
                                    $q->where('metode_pengadaan', $record->metode_pengadaan)
                                        ->orWhere('metode_pengadaan', 'semua');
                                })
                                ->where(function ($q) use ($record) {
                                    $q->where('jenis_belanja', $record->jenis_belanja)
                                        ->orWhere('jenis_belanja', 'semua');
                                })
                                ->orderBy('urutan')
                                ->get();

                            if ($syaratBelanjaList->isNotEmpty()) {
                                $uploadSyaratFields = [];
                                foreach ($syaratBelanjaList as $syarat) {
                                    $fieldKey = "dokumen_syarat_files.syarat_{$syarat->id}";
                                    $existingVal = data_get($record->dokumen_syarat_files, "syarat_{$syarat->id}");

                                    if (! $existingVal) {
                                        if (str_contains(strtolower($syarat->nama_syarat), 'nota') || str_contains(strtolower($syarat->nama_syarat), 'faktur')) {
                                            $existingVal = $record->file_nota_penjual;
                                        } elseif (str_contains(strtolower($syarat->nama_syarat), 'foto') || str_contains(strtolower($syarat->nama_syarat), 'dokumentasi')) {
                                            $existingVal = $record->file_dokumentasi_barang;
                                        } elseif (str_contains(strtolower($syarat->nama_syarat), 'e-purchasing') || str_contains(strtolower($syarat->nama_syarat), 'katalog')) {
                                            $existingVal = $record->file_ekatalog_bundel;
                                        }
                                    }

                                    $uploadSyaratFields[] = FileUpload::make($fieldKey)
                                        ->label("Unggah: {$syarat->nama_syarat}" . ($syarat->is_required ? ' (Wajib)' : ' (Opsional)'))
                                        ->helperText($syarat->keterangan)
                                        ->directory("spj/syarat-{$record->id}")
                                        ->default($existingVal)
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                        ->maxSize(10240)
                                        ->required((bool) $syarat->is_required && empty($existingVal));
                                }

                                $schema[] = Section::make('Kelengkapan Berkas & Dokumen SPJ')
                                    ->description('Unggah dokumen bukti belanja sesuai ketentuan resmi berikut:')
                                    ->compact()
                                    ->schema($uploadSyaratFields);
                            } else {
                                $schema[] = FileUpload::make('file_nota_penjual')
                                    ->label('Unggah Faktur / Nota Asli Penjual (Wajib untuk Proses Kwitansi)')
                                    ->directory('spj/nota-penjual')
                                    ->default($record->file_nota_penjual)
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5120)
                                    ->required(fn() => empty($record->file_nota_penjual));

                                $schema[] = FileUpload::make('file_dokumentasi_barang')
                                    ->label($wajibFotoBarang
                                        ? 'Unggah Foto Dokumentasi Fisik Barang (WAJIB karena nilai > Rp 2.000.000)'
                                        : 'Unggah Foto Dokumentasi Fisik Barang (Opsional)')
                                    ->directory('spj/dokumentasi-barang')
                                    ->default($record->file_dokumentasi_barang)
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5120)
                                    ->required(fn() => $wajibFotoBarang && empty($record->file_dokumentasi_barang));
                            }

                            if ((float) $record->total_nilai_realisasi > 0 && (float) $record->total_nilai_realisasi !== (float) $record->total_nilai_rencana) {
                                $schema[] = Textarea::make('justifikasi_selisih')
                                    ->label('Justifikasi Selisih Nilai Pesanan vs Kwitansi (Wajib)')
                                    ->default($record->justifikasi_selisih)
                                    ->required();
                            }
                        }

                        // Kasus 4: Checklist & Keputusan Pemeriksaan Pejabat
                        $isPemeriksaanStage = in_array($record->status, [
                            'diajukan',
                            'menunggu_verifikasi_pptk',
                            'menunggu_verifikasi_ppk',
                            'revisi_ppk',
                            'menunggu_persetujuan_pa',
                            'revisi_pa',
                            'menunggu_verifikasi_bendahara',
                            'revisi_bendahara',
                        ], true);

                        $isEkatalog = $record->metode_pengadaan === 'e_katalog';

                        // Ambil daftar syarat resmi dari MasterSyaratBelanja sesuai metode & jenis belanja transaksi ini
                        $masterSyaratList = \App\Models\MasterSyaratBelanja::query()
                            ->where('aktif', true)
                            ->where(function ($q) use ($record) {
                                $q->where('metode_pengadaan', $record->metode_pengadaan)
                                    ->orWhere('metode_pengadaan', 'semua');
                            })
                            ->where(function ($q) use ($record) {
                                $q->where('jenis_belanja', $record->jenis_belanja)
                                    ->orWhere('jenis_belanja', 'semua');
                            })
                            ->orderBy('urutan')
                            ->get();

                        $checklistOptions = [];

                        // 1. Tahap Surat Pesanan (saat diajukan ke PPTK)
                        if ($record->status === 'diajukan') {
                            $checklistOptions = $isEkatalog ? [
                                'sp1' => '[E-Katalog LKPP] Kesesuaian paket E-Purchasing LKPP dengan program kegiatan DPA/RKA',
                                'sp2' => '[E-Katalog LKPP] Ketersediaan pagu anggaran dinas dan total nilai paket pemesanan',
                                'sp3' => '[E-Katalog LKPP] Persetujuan dan otorisasi pemesanan paket pada sistem E-Katalog LKPP',
                            ] : [
                                'sp1' => '[Surat Pesanan] Kesesuaian rincian barang/jasa dengan kebutuhan kegiatan DPA/RKA',
                                'sp2' => '[Surat Pesanan] Ketersediaan pagu anggaran dan kewajaran kuantitas/harga pesanan',
                                'sp3' => '[Surat Pesanan] Persetujuan penerbitan dan penandatanganan Surat Pesanan kepada rekanan',
                            ];
                        }
                        // 2. Tahap Verifikasi Dokumen SPJ Lengkap oleh PPTK (100% SINKRON DENGAN MASTER SYARAT BELANJA)
                        elseif ($record->status === 'menunggu_verifikasi_pptk') {
                            $checklistOptions['core_pesanan'] = '[Surat Pesanan] Kesesuaian fisik barang yang diterima dengan Surat Pesanan resmi SAKTI';

                            if ($masterSyaratList->isNotEmpty()) {
                                foreach ($masterSyaratList as $ms) {
                                    $tag = $ms->is_required ? '[Wajib]' : '[Opsional]';
                                    $checklistOptions["syarat_{$ms->id}"] = "{$tag} {$ms->nama_syarat}";
                                }
                            } else {
                                // Fallback jika master syarat belum disetting
                                $checklistOptions['c_nota'] = '[Faktur/Nota] Memeriksa keaslian bukti faktur/nota dari toko penyedia';
                                $checklistOptions['c_kwitansi'] = '[Kwitansi] Memeriksa kesesuaian kwitansi pembayaran dan stempel lunas';
                                $checklistOptions['c_foto'] = '[Foto Barang] Memeriksa dokumentasi fisik barang pengadaan';
                            }

                            $checklistOptions['core_npd'] = '[Nota Pencairan Dana] Kesesuaian draf NPD dengan total pengeluaran belanja';
                        }
                        // 3. Tahap Verifikasi Dokumen SPJ & Kepatuhan Anggaran oleh PPK (100% SINKRON DENGAN MASTER SYARAT BELANJA)
                        elseif (in_array($record->status, ['menunggu_verifikasi_ppk', 'revisi_ppk'], true)) {
                            $checklistOptions['core_pesanan'] = '[Surat Pesanan] Kesesuaian berkas SPJ lengkap dengan Surat Pesanan resmi';

                            if ($masterSyaratList->isNotEmpty()) {
                                foreach ($masterSyaratList as $ms) {
                                    $tag = $ms->is_required ? '[Wajib]' : '[Opsional]';
                                    $checklistOptions["syarat_{$ms->id}"] = "{$tag} {$ms->nama_syarat}";
                                }
                            } else {
                                $checklistOptions['p_berkas'] = '[Kelengkapan Berkas SPJ] Memeriksa keabsahan bukti faktur toko dan foto dokumentasi';
                            }

                            $checklistOptions['p_dpa'] = '[Kesesuaian DPA/RKA] Keselarasan pembebanan Program, Kegiatan, dan Subkegiatan DPA dinas';
                            $checklistOptions['p_rekening'] = '[Kode Rekening Belanja] Ketepatan pembebanan kode rekening belanja daerah';
                            $checklistOptions['p_pagu'] = '[Ketersediaan Pagu] Memeriksa ketersediaan sisa pagu anggaran kas dinas';
                            $checklistOptions['p_pajak'] = '[Perhitungan Perpajakan] Memeriksa kebenaran pemotongan pajak (PPN 11% dan PPh 22 jika > Rp 2 Jt)';
                            $checklistOptions['core_npd'] = '[Nota Pencairan Dana] Kesesuaian nominal draf NPD antara Pesanan, Faktur, dan Kwitansi';
                        }
                        // 4. Tahap Pengesahan Akhir oleh PA (Kepala Dinas)
                        elseif (in_array($record->status, ['menunggu_persetujuan_pa', 'revisi_pa'], true)) {
                            $checklistOptions = [
                                'pa1' => '[Verifikasi Bertingkat] Memeriksa hasil penelitian dan pengesahan berjenjang dari PPTK dan PPK',
                                'pa2' => '[Kebenaran Beban APBD] Meneliti keabsahan belanja sebagai beban APBD dinas untuk kepentingan kedinasan',
                                'pa3' => '[Pengesahan Dokumen SPJ] Persetujuan dan penandatanganan Kwitansi Pembayaran dan Nota Pencairan Dana (NPD)',
                            ];
                        }
                        // 5. Tahap Uji Kas & Pencairan oleh Bendahara Pengeluaran
                        elseif (in_array($record->status, ['menunggu_verifikasi_bendahara', 'revisi_bendahara'], true)) {
                            $checklistOptions = [
                                'b1' => '[Keabsahan Tanda Tangan] Memeriksa keabsahan seluruh tanda tangan pejabat (PPTK, PPK, PA/Kadis, dan Rekanan)',
                                'b2' => '[Kelengkapan Bukti SPJ] Memeriksa kelengkapan fisik dokumen pertanggungjawaban belanja (SPJ)',
                                'b3' => '[Pungutan Pajak Negara] Meneliti pungutan pajak (PPN/PPh) yang disetorkan ke Kas Negara',
                                'b4' => '[Ketersediaan Kas Daerah] Memeriksa ketersediaan saldo kas pengeluaran dan mencatat pada Buku Kas Umum (BKU)',
                            ];
                        }

                        // LANGKAH 1: DAFTAR CHECKLIST UJI KEPATUHAN (DITAMPILKAN PERTAMA DI ATAS DENGAN CENTANG ✓ DAN SILANG ✗)
                        if ($isPemeriksaanStage && ! empty($checklistOptions)) {
                            $checklistFields = [];
                            foreach ($checklistOptions as $key => $label) {
                                $checklistFields[] = ToggleButtons::make("checklist.{$key}")
                                    ->label($label)
                                    ->options([
                                        'sesuai' => 'Sesuai',
                                        'tidak_sesuai' => 'Tidak Sesuai',
                                    ])
                                    ->icons([
                                        'sesuai' => 'heroicon-m-check-circle',
                                        'tidak_sesuai' => 'heroicon-m-x-circle',
                                    ])
                                    ->colors([
                                        'sesuai' => 'success',
                                        'tidak_sesuai' => 'danger',
                                    ])
                                    ->inline()
                                    ->default('sesuai')
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state, Get $get) use ($checklistOptions) {
                                        if ($state === 'tidak_sesuai') {
                                            $set('keputusan', 'revisi');
                                        } else {
                                            $allSesuai = true;
                                            foreach (array_keys($checklistOptions) as $otherKey) {
                                                if ($get("checklist.{$otherKey}") === 'tidak_sesuai') {
                                                    $allSesuai = false;
                                                    break;
                                                }
                                            }
                                            if ($allSesuai) {
                                                $set('keputusan', 'setuju');
                                            }
                                        }
                                    });
                            }

                            $schema[] = Section::make('Daftar Checklist Uji Kepatuhan Dokumen')
                                ->description('Tandai Centang (✓) atau Silang (✗) berdasarkan dokumen di sebelah kanan:')
                                ->compact()
                                ->schema($checklistFields);
                        }

                        // LANGKAH 2: KEPUTUSAN HASIL PEMERIKSAAN (DITAMPILKAN SETELAH CHECKLIST)
                        if ($isPemeriksaanStage) {
                            $schema[] = Section::make('Keputusan Hasil Pemeriksaan')
                                ->compact()
                                ->schema([
                                    Radio::make('keputusan')
                                        ->label('Pilihan Keputusan')
                                        ->options([
                                            'setuju' => '✓ Setujui Dokumen SPJ & Teruskan ke Tahapan Berikutnya',
                                            'revisi' => '⚠️ Kembalikan Berkas SPJ untuk Diperbaiki (Revisi)',
                                        ])
                                        ->default('setuju')
                                        ->live()
                                        ->required(),

                                    Textarea::make('catatan_revisi')
                                        ->label('Keterangan Alasan Pengembalian & Instruksi Perbaikan (Wajib Diisi)')
                                        ->placeholder('Sebutkan secara rinci butir yang tidak sesuai, kekurangan berkas, atau petunjuk yang wajib diperbaiki pemohon...')
                                        ->rows(4)
                                        ->visible(fn(Get $get): bool => $get('keputusan') === 'revisi')
                                        ->required(fn(Get $get): bool => $get('keputusan') === 'revisi'),

                                    Textarea::make('catatan')
                                        ->label('Catatan Pemeriksaan / Keterangan (Opsional)')
                                        ->placeholder('Tambahkan catatan atau paraf keterangan bila diperlukan...')
                                        ->visible(fn(Get $get): bool => $get('keputusan') !== 'revisi'),
                                ]);
                        }

                        // Kasus 5: Saat Bendahara mencairkan dana, wajib isi Nomor BKU & Tanggal Cair
                        if (in_array($record->status, ['menunggu_verifikasi_bendahara', 'revisi_bendahara'], true)) {
                            $schema[] = Section::make('Pencairan Kas & Buku Kas Umum')
                                ->compact()
                                ->visible(fn(Get $get): bool => $get('keputusan') !== 'revisi')
                                ->schema([
                                    TextInput::make('nomor_bku')
                                        ->label('Nomor Buku Kas Umum (BKU)')
                                        ->default($record->nomor_bku)
                                        ->placeholder('Contoh: BKU/2026/11/0045')
                                        ->required(fn(Get $get): bool => $get('keputusan') !== 'revisi'),

                                    DatePicker::make('tanggal_dicairkan')
                                        ->label('Tanggal Pencairan Kas')
                                        ->default($record->tanggal_dicairkan ?? now())
                                        ->displayFormat('d/m/Y')
                                        ->required(fn(Get $get): bool => $get('keputusan') !== 'revisi'),
                                ]);
                        }

                        if ($record->status !== 'draft') {
                            return [
                                Grid::make([
                                    'default' => 1,
                                    'lg' => 12,
                                    'xl' => 12,
                                ])
                                    ->schema([
                                        // KOLOM KIRI (5 kolom): FORMULIR EVALUASI, CHECKLIST, & KEPUTUSAN
                                        Group::make($schema)
                                            ->columnSpan([
                                                'default' => 1,
                                                'lg' => 5,
                                                'xl' => 5,
                                            ]),

                                        // KOLOM KANAN (7 kolom): DOKUMEN SPJ (KONSISTEN DENGAN POSISI PREVIEW CETAK)
                                        Group::make([
                                            View::make('filament.components.pedoman-verifikasi-spj')
                                                ->viewData([
                                                    'record' => $record,
                                                ]),
                                        ])->columnSpan([
                                            'default' => 1,
                                            'lg' => 7,
                                            'xl' => 7,
                                        ]),
                                    ]),
                            ];
                        }

                        return $schema;
                    })
                    ->action(function (PengajuanSpj $record, array $data, SpjWorkflowService $workflowService): void {
                        if (($data['keputusan'] ?? 'setuju') === 'revisi') {
                            $revisiStatus = match ($record->status) {
                                'diajukan' => 'draft',
                                'menunggu_verifikasi_pptk' => 'revisi_pptk',
                                'menunggu_verifikasi_ppk', 'revisi_ppk' => 'revisi_ppk',
                                'menunggu_persetujuan_pa', 'revisi_pa' => 'revisi_pa',
                                'menunggu_verifikasi_bendahara', 'revisi_bendahara' => 'revisi_bendahara',
                                default => 'draft',
                            };

                            $peran = match ($record->status) {
                                'diajukan', 'menunggu_verifikasi_pptk' => 'pptk',
                                'menunggu_verifikasi_ppk', 'revisi_ppk' => 'ppk',
                                'menunggu_persetujuan_pa', 'revisi_pa' => 'pa',
                                'menunggu_verifikasi_bendahara', 'revisi_bendahara' => 'bendahara',
                                default => 'pemeriksa',
                            };

                            $workflowService->transition(
                                $record,
                                $revisiStatus,
                                $data['catatan_revisi'] ?? 'Dikembalikan untuk diperbaiki.',
                                $peran,
                                null
                            );

                            Notification::make()
                                ->title('Berkas SPJ Berhasil Dikembalikan')
                                ->body("Dokumen SPJ dikembalikan dengan status: {$record->fresh()->status_label}")
                                ->warning()
                                ->send();

                            return;
                        }

                        // Simpan data tambahan pada tahapan terkait sebelum transisi status
                        $extraUpdates = [];
                        foreach (['penerima_nama', 'penerima_identitas', 'file_nota_penjual', 'file_dokumentasi_barang', 'justifikasi_selisih', 'nomor_bku', 'tanggal_dicairkan'] as $field) {
                            if (! empty($data[$field])) {
                                $extraUpdates[$field] = $data[$field];
                            }
                        }

                        if (! empty($data['dokumen_syarat_files']) && is_array($data['dokumen_syarat_files'])) {
                            $existingFiles = (array) ($record->dokumen_syarat_files ?? []);
                            $mergedFiles = array_merge($existingFiles, array_filter($data['dokumen_syarat_files']));
                            $extraUpdates['dokumen_syarat_files'] = $mergedFiles;

                            foreach ($mergedFiles as $k => $filePath) {
                                if (str_contains($k, 'syarat_')) {
                                    $syaratId = (int) str_replace('syarat_', '', $k);
                                    $syaratItem = \App\Models\MasterSyaratBelanja::find($syaratId);
                                    if ($syaratItem) {
                                        $syaratLower = strtolower($syaratItem->nama_syarat);
                                        if (str_contains($syaratLower, 'nota') || str_contains($syaratLower, 'faktur')) {
                                            $extraUpdates['file_nota_penjual'] = $filePath;
                                        } elseif (str_contains($syaratLower, 'foto') || str_contains($syaratLower, 'dokumentasi')) {
                                            $extraUpdates['file_dokumentasi_barang'] = $filePath;
                                        } elseif (str_contains($syaratLower, 'e-purchasing') || str_contains($syaratLower, 'katalog')) {
                                            $extraUpdates['file_ekatalog_bundel'] = $filePath;
                                        }
                                    }
                                }
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
                            ->title('Tahapan SPJ Berhasil Diproses')
                            ->body("Status berkas saat ini: {$record->fresh()->status_label}")
                            ->success()
                            ->send();
                    }),

                // Aksi Menu Tambahan (Ubah, Lihat, Hapus) dirangkum rapi ke dalam ActionGroup Menu Titik Tiga
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat Detail')
                        ->icon(static::safeIcon('fas-eye', 'heroicon-o-eye')),

                    EditAction::make()
                        ->label('Ubah Berkas')
                        ->icon(static::safeIcon('fas-pen-to-square', 'heroicon-o-pencil'))
                        ->visible(function (PengajuanSpj $record): bool {
                            if (in_array($record->status, ['disetujui', 'dicairkan', 'ditolak'], true)) {
                                return false;
                            }

                            /** @var \App\Models\User|null $user */
                            $user = Auth::user();
                            if (! $user) {
                                return false;
                            }

                            if ($user->hasAnyRole(['super_admin', 'admin'])) {
                                return true;
                            }

                            $anggaranRole = $user->anggaran_role instanceof \App\Enums\AnggaranRole
                                ? $user->anggaran_role->value
                                : $user->anggaran_role;

                            $isPptkThisRecord = ($user->hasRole('pptk') || $anggaranRole === 'PPTK')
                                && ($user->id === $record->pptk_user_id || ((int) $user->bidang_id > 0 && (int) $user->bidang_id === (int) $record->bidang_id) || (int) $record->bidang?->kepala_id === (int) $user->id || $user->hasRole('super_admin'));

                            return match ($record->status) {
                                'draft' => $user->hasAnyRole(['jf', 'pegawai']) || $user->id === $record->user_id,
                                'diajukan' => $isPptkThisRecord,
                                'menunggu_realisasi', 'revisi_pptk' => $user->hasAnyRole(['jf', 'pegawai']) || $user->id === $record->user_id,
                                'menunggu_verifikasi_pptk' => $isPptkThisRecord,
                                'menunggu_verifikasi_ppk', 'revisi_ppk' => $user->hasRole('ppk') || $anggaranRole === 'PPK' || $user->id === $record->ppk_user_id,
                                'menunggu_persetujuan_pa', 'revisi_pa' => $user->hasRole('pa') || $anggaranRole === 'PA' || $user->id === $record->pa_user_id,
                                'menunggu_verifikasi_bendahara', 'revisi_bendahara' => $user->hasRole('bendahara') || $anggaranRole === 'BENDAHARA' || $user->id === $record->bendahara_user_id,
                                default => true,
                            };
                        }),

                    DeleteAction::make()
                        ->label('Hapus Draf')
                        ->icon(static::safeIcon('fas-trash-can', 'heroicon-o-trash'))
                        ->visible(fn(PengajuanSpj $record): bool => $record->status === 'draft'),
                ])
                    ->dropdownPlacement('bottom-start')
                    ->tooltip('Menu Aksi Lainnya')
                    ->icon(static::safeIcon('fas-ellipsis-vertical', 'heroicon-m-ellipsis-vertical'))
                    ->color('gray'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Pengajuan SPJ')
                    ->icon(static::safeIcon('fas-circle-plus', 'heroicon-o-plus-circle'))
                    ->color('primary')
                    ->url(\App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource::getUrl('create')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
