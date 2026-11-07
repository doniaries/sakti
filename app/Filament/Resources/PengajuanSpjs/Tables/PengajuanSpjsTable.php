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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PengajuanSpjsTable
{
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
                $canViewAllBidang = $user !== null && $user->can('viewAny', Bidang::class);

                $anggaranRole = $user?->anggaran_role instanceof \App\Enums\AnggaranRole
                    ? $user->anggaran_role->value
                    : $user?->anggaran_role;

                $isGlobalRole = in_array($anggaranRole, ['PA', 'PPK', 'BENDAHARA', 'PPTK'], true)
                    || ($user !== null && $user->hasAnyRole(['pa', 'ppk', 'pptk', 'bendahara', 'super_admin', 'admin']))
                    || str_contains(strtolower($user?->jabatan ?? ''), 'kepala dinas')
                    || str_contains(strtolower($user?->jabatan ?? ''), 'sekretaris')
                    || str_contains(strtolower($user?->jabatan ?? ''), 'kepala bidang');

                if (! $canViewAllBidang && ! $isGlobalRole && $user !== null) {
                    $userBidangId = filled($user->bidang_id)
                        ? (int) $user->bidang_id
                        : (int) (Bidang::where('kepala_id', $user->id)->value('id') ?? 0);

                    if ($userBidangId > 0) {
                        $query->where(function (Builder $q) use ($user, $userBidangId) {
                            $q->where('bidang_id', $userBidangId)
                              ->orWhere('user_id', $user->id);
                        });
                    }
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
            ->stickyableColumns()
            ->columns([
                TextColumn::make('tahun_anggaran')
                    ->label('Tahun')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('nomor_pengajuan')
                    ->label('No. Pengajuan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->sticky(),

                TextColumn::make('tanggal_pengajuan')
                    ->label('Tgl Pengajuan')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('metode_pengadaan')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'e_katalog' => 'E-Katalog',
                        default => 'Non-Katalog',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'e_katalog' => 'info',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('jenis_belanja')
                    ->label('Jenis Belanja')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'barang_jasa' => 'Barang & Jasa',
                        'makan_minum' => 'Makan Minum',
                        'sppd' => 'SPPD',
                        'bbm' => 'BBM',
                        default => 'Barang & Jasa',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'barang_jasa' => 'primary',
                        'makan_minum' => 'warning',
                        'sppd' => 'success',
                        'bbm' => 'danger',
                        default => 'primary',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->badge()
                    ->color(fn(?string $state): string => Bidang::badgeColor($state))
                    ->searchable()
                    ->sortable()
                    ->stickyable(),

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
                    ->color(fn(PengajuanSpj $record): string => $record->status_color)
                    ->formatStateUsing(fn(PengajuanSpj $record): string => $record->status_label)
                    ->sortable(),
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
                        'menunggu_realisasi' => 'Pesanan Disetujui / Menyiapkan Berkas SPJ',
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
                // Aksi 1: Preview & Cetak Dokumen Bertahap dengan Modal Preview Interaktif
                Action::make('cetak_dokumen')
                    ->label(fn(PengajuanSpj $record): string => match ($record->status) {
                        'menunggu_realisasi' => 'Cetak Pesanan',
                        default => 'Cetak SPJ',
                    })
                    ->tooltip(fn(PengajuanSpj $record): string => match ($record->status) {
                        'menunggu_realisasi' => 'Pratinjau & Cetak Surat Pesanan Barang (F4)',
                        default => 'Pratinjau & Cetak Dokumen SPJ Lengkap (Pesanan, Kwitansi, NPD)',
                    })
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('info')
                    ->visible(fn(PengajuanSpj $record): bool => ! in_array($record->status, ['draft', 'diajukan', 'ditolak'], true))
                    ->modalHeading(fn(PengajuanSpj $record): string => "Pratinjau Dokumen SPJ — {$record->nomor_pengajuan}")
                    ->modalWidth(\Filament\Support\Enums\Width::SevenExtraLarge)
                    ->modalContent(fn(PengajuanSpj $record) => view('filament.pages.preview-spj-modal', [
                        'record' => $record,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                // Aksi 2.A: Ajukan Pesanan Langsung (Konfirmasi Cepat 1-Klik tanpa Modal Form Simpan)
                Action::make('ajukan_pesanan')
                    ->label('Ajukan Pesanan')
                    ->tooltip('Ajukan draf surat pesanan ke PPTK / Kepala Bidang')
                    ->icon('heroicon-o-paper-airplane')
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
                    ->tooltip('Batalkan pengajuan ini dan kembalikan ke status Draft untuk diedit kembali')
                    ->icon('heroicon-o-arrow-uturn-left')
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
                            'diajukan' => 'Setujui Pesanan',
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
                            'diajukan' => 'Persetujuan & Tanda Tangan Surat Pesanan oleh PPTK',
                            'menunggu_realisasi', 'revisi_pptk' => 'Unggah faktur toko, dokumentasi barang, dan lengkapi berkas SPJ',
                            'menunggu_verifikasi_pptk' => 'Pemeriksaan kepatuhan berkas SPJ lengkap oleh PPTK',
                            'menunggu_verifikasi_ppk', 'revisi_ppk' => 'Verifikasi berkas SPJ, kode rekening, dan perpajakan oleh PPK',
                            'menunggu_persetujuan_pa', 'revisi_pa' => 'Persetujuan akhir dan pengesahan SPJ oleh Pengguna Anggaran (Kadis)',
                            'menunggu_verifikasi_bendahara', 'revisi_bendahara' => 'Pemeriksaan uji kas dan pencairan dana belanja oleh Bendahara',
                            default => 'Proses tahapan berkas SPJ ini',
                        };
                    })
                    ->modalHeading(fn(PengajuanSpj $record): string => match ($record->status) {
                        'diajukan' => 'Persetujuan & TTD Surat Pesanan (PPTK)',
                        'menunggu_realisasi', 'revisi_pptk' => 'Proses Kelengkapan Berkas SPJ',
                        'menunggu_verifikasi_pptk' => 'Verifikasi Berkas Dokumen SPJ (PPTK)',
                        'menunggu_verifikasi_ppk', 'revisi_ppk' => 'Verifikasi Berkas Dokumen SPJ (PPK)',
                        'menunggu_persetujuan_pa', 'revisi_pa' => 'Persetujuan & Pengesahan SPJ (PA / Kepala Dinas)',
                        'menunggu_verifikasi_bendahara', 'revisi_bendahara' => 'Uji Kas & Pencairan Dokumen SPJ (Bendahara)',
                        default => 'Pemeriksaan Dokumen SPJ',
                    })
                    ->icon(function (PengajuanSpj $record): string {
                        return match ($record->status) {
                            'diajukan' => 'heroicon-o-pencil-square',
                            'menunggu_realisasi', 'revisi_pptk' => 'heroicon-o-receipt-percent',
                            'menunggu_verifikasi_bendahara', 'revisi_bendahara' => 'heroicon-o-banknotes',
                            default => 'heroicon-o-check-circle',
                        };
                    })
                    ->color(function (PengajuanSpj $record): string {
                        return match ($record->status) {
                            'diajukan' => 'info',
                            'menunggu_realisasi', 'revisi_pptk' => 'primary',
                            default => 'success',
                        };
                    })
                    ->modalWidth(\Filament\Support\Enums\Width::SevenExtraLarge)
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

                // Aksi 3: Kembalikan untuk Revisi (Hanya muncul saat di meja pemeriksa)
                Action::make('kembalikan_revisi')
                    ->label(fn(PengajuanSpj $record): string => $record->status === 'diajukan' ? 'Revisi Pesanan' : 'Kembalikan')
                    ->tooltip('Kembalikan berkas SPJ ke pemohon / tahapan sebelumnya untuk diperbaiki')
                    ->modalHeading(fn(PengajuanSpj $record): string => "Kembalikan Berkas SPJ untuk Diperbaiki — {$record->nomor_pengajuan}")
                    ->modalDescription('Dokumen SPJ akan dikembalikan ke pembuat pengajuan / tahapan sebelumnya beserta instruksi perbaikan yang Anda masukkan.')
                    ->modalSubmitActionLabel('Kembalikan Berkas (Revisi)')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(function (PengajuanSpj $record): bool {
                        if (! in_array($record->status, [
                            'diajukan',
                            'menunggu_verifikasi_pptk',
                            'menunggu_verifikasi_ppk',
                            'menunggu_persetujuan_pa',
                            'menunggu_verifikasi_bendahara',
                        ], true)) {
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
                            'diajukan', 'menunggu_verifikasi_pptk' => $isPptkThisRecord,
                            'menunggu_verifikasi_ppk' => $user->hasRole('ppk') || $anggaranRole === 'PPK' || $user->id === $record->ppk_user_id,
                            'menunggu_persetujuan_pa' => $user->hasRole('pa') || $anggaranRole === 'PA' || $user->id === $record->pa_user_id,
                            'menunggu_verifikasi_bendahara' => $user->hasRole('bendahara') || $anggaranRole === 'BENDAHARA' || $user->id === $record->bendahara_user_id,
                            default => true,
                        };
                    })
                    ->form([
                        Textarea::make('catatan_revisi')
                            ->label('Keterangan Alasan Pengembalian & Instruksi Perbaikan (Wajib Diisi)')
                            ->placeholder('Sebutkan secara rinci item pesanan, berkas lampiran, atau perhitungan pajak/nominal yang harus diperbaiki...')
                            ->rows(4)
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

                        $peran = match ($record->status) {
                            'diajukan', 'menunggu_verifikasi_pptk' => 'pptk',
                            'menunggu_verifikasi_ppk' => 'ppk',
                            'menunggu_persetujuan_pa' => 'pa',
                            'menunggu_verifikasi_bendahara' => 'bendahara',
                            default => 'pemeriksa',
                        };

                        $workflowService->transition(
                            $record,
                            $revisiStatus,
                            $data['catatan_revisi'],
                            $peran
                        );

                        Notification::make()
                            ->title('Berkas SPJ Berhasil Dikembalikan')
                            ->body("Dokumen telah berstatus: {$record->fresh()->status_label}")
                            ->warning()
                            ->send();
                    }),

                // Aksi Menu Tambahan (Ubah, Lihat, Hapus) dirangkum rapi ke dalam ActionGroup Menu Titik Tiga
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat Detail'),

                    EditAction::make()
                        ->label('Ubah Berkas')
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
                        ->visible(fn(PengajuanSpj $record): bool => $record->status === 'draft'),
                ])
                ->tooltip('Menu Aksi Lainnya')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Pengajuan SPJ')
                    ->icon('heroicon-o-plus-circle')
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
