<?php

namespace App\Filament\Resources\MasterSubKegiatans\Pages;

use App\Filament\Resources\KodeRekenings\KodeRekeningResource;
use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\MasterPrograms\MasterProgramResource;
use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Models\Bidang;
use App\Models\MasterSubKegiatan;
use App\Services\Rka\SipdRkaImporterService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use ZeeshanTariq\FilamentStickyColumns\Concerns\InteractsWithStickyableColumns;

class ListMasterSubKegiatans extends ListRecords
{
    use InteractsWithStickyableColumns;

    protected static string $resource = MasterSubKegiatanResource::class;

    /**
     * Filter Tab Bidang di Header Tabel untuk kemudahan navigasi & menghilangkan filter dropdown yang berantakan.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $isGlobal = $user && $user->canViewAllBidangKeuangan();

        $selectedTahun = \App\Models\PengaturanAplikasi::getTahunAnggaranAktif();

        // 1. Khusus JF: Hanya tampilkan tab tugasnya sendiri
        if (! $isGlobal && $user && $user->hasRole('jf') && ! $user->hasRole('pptk')) {
            $userBidangId = (int) ($user->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $user->id)->value('id') ?? 0));
            $count = MasterSubKegiatan::where('tahun_anggaran', (int) $selectedTahun)
                ->where(function ($q) use ($user, $userBidangId) {
                    $q->where('jf_id', $user->id);
                    if ($userBidangId > 0) {
                        $q->orWhere(function ($subQ) use ($userBidangId, $user) {
                            $subQ->whereHas('kegiatan', fn ($kq) => $kq->where('bidang_id', $userBidangId))
                                 ->where(fn ($sq) => $sq->whereNull('jf_id')->orWhere('jf_id', $user->id));
                        });
                    }
                })
                ->count();

            return [
                'tugas_saya' => Tab::make('Sub Kegiatan')
                    ->icon('heroicon-m-user')
                    ->badge($count)
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('jf_id', $user->id)),
            ];
        }

        // 2. PPTK / Kepala Bidang: Tampilkan HANYA tab nama bidangnya saja
        if (! $isGlobal && $user?->bidang_id) {
            $bidang = Bidang::find($user->bidang_id);
            if ($bidang) {
                $icon = match ($bidang->slug) {
                    'sekretariat' => 'heroicon-m-building-office',
                    'pemasaran-pariwisata', 'bidang-pemasaran-pariwisata' => 'heroicon-m-megaphone',
                    'pengembangan-destinasi-pariwisata' => 'heroicon-m-map-pin',
                    'pemuda-dan-olahraga' => 'heroicon-m-trophy',
                    default => 'heroicon-m-briefcase',
                };

                $count = MasterSubKegiatan::where('tahun_anggaran', (int) $selectedTahun)
                    ->whereHas('kegiatan', fn($q) => $q->where('bidang_id', $bidang->id))
                    ->count();

                return [
                    $bidang->slug ?: 'bidang_' . $bidang->id => Tab::make($bidang->nama)
                        ->icon($icon)
                        ->badge($count)
                        ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('kegiatan', fn($q) => $q->where('bidang_id', $bidang->id))),
                ];
            }
        }

        $baseQuery = MasterSubKegiatan::query()->where('tahun_anggaran', (int) $selectedTahun);

        $tabs = [
            'semua' => Tab::make('Semua')
                ->icon('heroicon-m-squares-2x2')
                ->badge($baseQuery->count()),
        ];

        $bidangQuery = Bidang::query()->orderBy('nama');
        $bidangs = $bidangQuery->get();

        $countsPerBidang = MasterSubKegiatan::query()
            ->join('master_kegiatan', 'master_sub_kegiatan.kegiatan_id', '=', 'master_kegiatan.id')
            ->where('master_sub_kegiatan.tahun_anggaran', (int) $selectedTahun)
            ->groupBy('master_kegiatan.bidang_id')
            ->selectRaw('master_kegiatan.bidang_id, count(*) as aggregate')
            ->pluck('aggregate', 'bidang_id');

        foreach ($bidangs as $bidang) {
            $icon = match ($bidang->slug) {
                'sekretariat' => 'heroicon-m-building-office',
                'pemasaran-pariwisata', 'bidang-pemasaran-pariwisata' => 'heroicon-m-megaphone',
                'pengembangan-destinasi-pariwisata' => 'heroicon-m-map-pin',
                'pemuda-dan-olahraga' => 'heroicon-m-trophy',
                default => 'heroicon-m-briefcase',
            };

            $count = (int) ($countsPerBidang[$bidang->id] ?? 0);

            $tabs[$bidang->slug ?: 'bidang_' . $bidang->id] = Tab::make($bidang->nama)
                ->icon($icon)
                ->badge($count)
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('kegiatan', fn($q) => $q->where('bidang_id', $bidang->id)));
        }

        $countBelumAdaBidang = (clone $baseQuery)->where(function ($q) {
            $q->whereDoesntHave('kegiatan')
                ->orWhereHas('kegiatan', fn($kq) => $kq->whereNull('bidang_id'));
        })->count();

        if ($countBelumAdaBidang > 0) {
            $tabs['belum_ada_bidang'] = Tab::make('Belum Ditugaskan Bidang')
                ->icon('heroicon-m-exclamation-triangle')
                ->badge($countBelumAdaBidang)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->where(function ($q) {
                    $q->whereDoesntHave('kegiatan')
                        ->orWhereHas('kegiatan', fn($kq) => $kq->whereNull('bidang_id'));
                }));
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        $tahunAktif = (int) \App\Models\PengaturanAplikasi::getTahunAnggaranAktif();
        $isAllLocked = ! MasterSubKegiatan::where('tahun_anggaran', $tahunAktif)->where('is_locked', false)->exists();

        return [
            Action::make('global_lock_dpa')
                ->label($isAllLocked ? "Buka Kunci DPA ({$tahunAktif})" : "Kunci DPA ({$tahunAktif})")
                ->icon($isAllLocked ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                ->color($isAllLocked ? 'success' : 'danger')
                ->visible(fn(): bool => auth()->user()?->hasRole('super_admin') ?? false)
                ->requiresConfirmation()
                ->modalHeading($isAllLocked ? "Buka Kunci Seluruh Sub Kegiatan Tahun {$tahunAktif}?" : "Kunci Seluruh Sub Kegiatan Tahun {$tahunAktif}?")
                ->modalDescription($isAllLocked
                    ? "Tindakan ini akan membuka proteksi seluruh Sub Kegiatan Tahun Anggaran {$tahunAktif} agar data belanja RKA dapat disesuaikan / dilakukan pergeseran anggaran."
                    : "Tindakan ini akan mengunci seluruh Sub Kegiatan Tahun Anggaran {$tahunAktif} sehingga data pagu RKA terlindungi dari perubahan sesuai DPA resmi.")
                ->modalSubmitActionLabel($isAllLocked ? 'Ya, Buka Kunci DPA' : 'Ya, Kunci DPA Sekarang')
                ->action(function () use ($tahunAktif, $isAllLocked): void {
                    $newStatus = ! $isAllLocked;

                    $affected = MasterSubKegiatan::where('tahun_anggaran', $tahunAktif)
                        ->update(['is_locked' => $newStatus]);

                    Notification::make()
                        ->title($newStatus ? "Berhasil Mengunci Seluruh DPA ({$tahunAktif})" : "Berhasil Membuka Kunci Seluruh DPA ({$tahunAktif})")
                        ->body($newStatus
                            ? "Sebanyak {$affected} Sub Kegiatan Tahun {$tahunAktif} berhasil dikunci dan diproteksi sesuai DPA resmi."
                            : "Sebanyak {$affected} Sub Kegiatan Tahun {$tahunAktif} berhasil dibuka untuk mode pergeseran anggaran.")
                        ->color($newStatus ? 'danger' : 'success')
                        ->send();
                }),

            CreateAction::make()
                ->label('Tambah Sub Kegiatan')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->visible(fn() => static::getResource()::canCreate()),

            Action::make('import_pdf_rka')
                ->label('Import RKA (PDF SIPD)')
                ->icon('heroicon-o-document-arrow-up')
                ->color('success')
                ->visible(fn() => static::getResource()::canCreate())
                ->modalHeading('Import RKA Sub Kegiatan dari PDF SIPD RI')
                ->modalDescription('Unggah dokumen resmi "Cetak RKA Rincian Belanja" format PDF yang diunduh langsung dari portal SIPD RI. Sistem otomatis mengekstrak Program, Kegiatan, Sub Kegiatan, dan seluruh rincian belanja.')
                ->modalSubmitActionLabel('Mulai Ekstraksi & Import')
                ->schema([
                    FileUpload::make('file_pdf')
                        ->label('Dokumen PDF RKA SIPD')
                        ->acceptedFileTypes(['application/pdf'])
                        ->disk('public')
                        ->directory('temp_rka_imports')
                        ->getUploadedFileNameForStorageUsing(fn(\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string => (string) \Illuminate\Support\Str::uuid() . '.pdf')
                        ->helperText('Sistem otomatis mengekstrak Program, Kegiatan, Sub Kegiatan, Kode Rekening, Item Belanja, Koefisien, dan Harga.')
                        ->required(),

                    Select::make('bidang_id')
                        ->label('Bidang Pengampu (Opsional)')
                        ->options(fn() => Bidang::pluck('nama', 'id')->all())
                        ->placeholder('Pilih Bidang (atau biarkan kosong)')
                        ->helperText('Jika dikosongkan, sistem akan mencoba memetakan berdasarkan struktur OPD atau dibagikan manual oleh Kadis/Sekretaris.')
                        ->nullable(),

                    Toggle::make('timpa_jika_ada')
                        ->label('Perbarui jika sudah pernah diimpor (Revisi RKA)')
                        ->default(false)
                        ->helperText('Jika dinonaktifkan, sistem akan otomatis menolak jika dokumen RKA Sub Kegiatan ini sudah pernah diunggah sebelumnya.'),
                ])
                ->action(function (array $data): void {
                    $filePath = is_array($data['file_pdf']) ? (array_values($data['file_pdf'])[0] ?? null) : $data['file_pdf'];
                    if (! $filePath) {
                        Notification::make()->title('File PDF tidak ditemukan')->danger()->send();
                        return;
                    }
                    $path = Storage::disk('public')->path($filePath);
                    try {
                        $targetBidangId = filled($data['bidang_id'] ?? null) ? (int) $data['bidang_id'] : null;
                        $allowOverwrite = (bool) ($data['timpa_jika_ada'] ?? false);
                        $result = SipdRkaImporterService::import($path, true, $targetBidangId, $allowOverwrite);
                        Notification::make()
                            ->title('Import RKA Berhasil!')
                            ->body("Berhasil mengimpor {$result['imported_items']} item rincian belanja untuk Sub Kegiatan: [{$result['kode_sub_kegiatan']}] {$result['sub_kegiatan']} (Total Pagu: Rp " . number_format($result['total_pagu'], 0, ',', '.') . ")")
                            ->success()
                            ->send();
                    } catch (\DomainException $e) {
                        Notification::make()
                            ->title('RKA Sudah Pernah Diimpor!')
                            ->body($e->getMessage())
                            ->warning()
                            ->persistent()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Mengimpor RKA')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        if (file_exists($path)) {
                            @unlink($path);
                        }
                    }
                }),
        ];
    }
}
