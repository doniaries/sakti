<?php

namespace App\Filament\Resources\RincianAnggaranBelanjas\Pages;

use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\RincianAnggaranBelanjas\RincianAnggaranBelanjaResource;
use App\Models\Bidang;
use App\Models\RincianAnggaranBelanja;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ListRincianAnggaranBelanjas extends ListRecords
{
    protected static string $resource = RincianAnggaranBelanjaResource::class;

    public function mount(): void
    {
        redirect()->to(MasterSubKegiatanResource::getUrl('index'));
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Input Rincian Belanja (RKA)')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),

            Action::make('import_pdf_rka')
                ->label('Import RKA/DPA (PDF SIPD)')
                ->icon('heroicon-o-document-arrow-up')
                ->color('success')
                ->modalHeading('Import Rincian Belanja dari PDF SIPD RI')
                ->modalDescription('Unggah dokumen resmi "Cetak RKA Rincian Belanja" format PDF yang diunduh langsung dari portal SIPD RI.')
                ->modalSubmitActionLabel('Mulai Ekstraksi & Import')
                ->schema([
                    FileUpload::make('file_pdf')
                        ->label('Dokumen PDF RKA / DPA SIPD (Bisa Pilih Banyak)')
                        ->acceptedFileTypes(['application/pdf'])
                        ->disk('public')
                        ->directory('temp_rka_imports')
                        ->multiple()
                        ->maxFiles(50)
                        ->maxSize(102400)
                        ->getUploadedFileNameForStorageUsing(fn(\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string => (string) \Illuminate\Support\Str::uuid() . '.pdf')
                        ->helperText('Format PDF resmi SIPD RI. Anda dapat memilih satu atau banyak berkas PDF sekaligus (Maksimal 100 MB per berkas).')
                        ->required(),

                    Select::make('bidang_id')
                        ->label('Bidang  (Opsional)')
                        ->options(fn() => Bidang::pluck('nama', 'id')->all())
                        ->placeholder('Pilih Bidang (atau biarkan kosong untuk pembagian kerja manual)')
                        ->helperText('Jika dikosongkan, data akan berstatus "Belum Ditugaskan" agar Kadis/Sekretaris dapat membagikannya secara manual.')
                        ->nullable(),

                    Toggle::make('timpa_jika_ada')
                        ->label('Perbarui jika sudah pernah diimpor (Revisi RKA)')
                        ->default(false)
                        ->helperText('Jika dinonaktifkan, sistem akan otomatis melewati dokumen jika Sub Kegiatan ini sudah pernah diunggah sebelumnya.'),
                ])
                ->action(function (array $data): void {
                    $rawFiles = $data['file_pdf'] ?? [];
                    $fileList = is_array($rawFiles) ? array_values($rawFiles) : [$rawFiles];
                    $fileList = array_filter($fileList);

                    if (empty($fileList)) {
                        Notification::make()->title('File PDF tidak ditemukan')->danger()->send();
                        return;
                    }

                    $targetBidangId = filled($data['bidang_id'] ?? null) ? (int) $data['bidang_id'] : null;
                    $allowOverwrite = (bool) ($data['timpa_jika_ada'] ?? false);

                    $successCount = 0;
                    $skippedCount = 0;
                    $errorCount = 0;
                    $details = [];

                    foreach ($fileList as $filePath) {
                        $path = Storage::disk('public')->path($filePath);
                        try {
                            $result = SipdRkaImporterService::import($path, true, $targetBidangId, $allowOverwrite);
                            $successCount++;
                            $details[] = "✅ [{$result['kode_sub_kegiatan']}] {$result['sub_kegiatan']} ({$result['imported_items']} item - Rp " . number_format($result['total_pagu'], 0, ',', '.') . ")";
                        } catch (\DomainException $e) {
                            $skippedCount++;
                            $details[] = "⚠️ " . $e->getMessage();
                        } catch (\Throwable $e) {
                            $errorCount++;
                            $details[] = "❌ " . basename($filePath) . ": " . $e->getMessage();
                        } finally {
                            if (file_exists($path)) {
                                @unlink($path);
                            }
                        }
                    }

                    if ($successCount > 0) {
                        $bodyMsg = "Berhasil memproses {$successCount} dokumen Sub Kegiatan.";
                        if ($skippedCount > 0) {
                            $bodyMsg .= " ({$skippedCount} dilewati karena sudah ada).";
                        }
                        if ($errorCount > 0) {
                            $bodyMsg .= " ({$errorCount} gagal diproses).";
                        }

                        Notification::make()
                            ->title("Import RKA/DPA Selesai ({$successCount}/" . count($fileList) . " Berhasil)")
                            ->body($bodyMsg . "\n\n" . implode("\n", array_slice($details, 0, 5)) . (count($details) > 5 ? "\n... dan " . (count($details) - 5) . " lainnya." : ''))
                            ->success()
                            ->persistent()
                            ->send();
                    } elseif ($skippedCount > 0 && $errorCount === 0) {
                        Notification::make()
                            ->title('Dokumen Sudah Pernah Diimpor!')
                            ->body("Seluruh ({$skippedCount}) dokumen telah ada di sistem. Aktifkan opsi 'Perbarui jika sudah pernah diimpor' jika ingin memperbarui.")
                            ->warning()
                            ->persistent()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Gagal Mengimpor Dokumen')
                            ->body(implode("\n", array_slice($details, 0, 5)))
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\RincianAnggaranBelanjas\Widgets\RkaOverviewWidget::class,
        ];
    }

    /**
     * Tab Filter Bidang:
     * - PPTK (Kepala Bidang) & Staf Bidang: Hanya melihat tab nama bidangnya saja.
     * - Pemeriksa (PPK, PA, Bendahara, Super Admin): Melihat tab 'Semua', seluruh bidang, dan 'Belum Ditugaskan'.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $canViewAll = $user?->canViewAllBidangKeuangan() ?? false;

        $selectedTahun = request()->query('tahun')
            ?? data_get(request()->query('filters'), 'tahun_anggaran.value')
            ?? data_get(request()->query('tableFilters'), 'tahun_anggaran.value')
            ?? (RincianAnggaranBelanja::query()->max('tahun_anggaran') ?? (int) date('Y'));

        // Jika PPTK / non-pemeriksa, kunci hanya pada bidang miliknya
        if (! $canViewAll && $user?->bidang_id) {
            $bidang = Bidang::find($user->bidang_id);
            if ($bidang) {
                $count = RincianAnggaranBelanja::where('tahun_anggaran', (int) $selectedTahun)
                    ->where('bidang_id', $bidang->id)
                    ->count();

                return [
                    $bidang->slug ?: 'bidang_' . $bidang->id => Tab::make($bidang->nama)
                        ->icon('heroicon-m-briefcase')
                        ->badge($count)
                        ->modifyQueryUsing(fn(Builder $query) => $query->where('bidang_id', $bidang->id)),
                ];
            }
        }

        // Untuk Pemeriksa (PPK, PA, Bendahara, Super Admin)
        $tabs = [
            'semua' => Tab::make('Semua Bidang')
                ->icon('heroicon-m-squares-2x2')
                ->badge(RincianAnggaranBelanja::where('tahun_anggaran', (int) $selectedTahun)->count()),

            'belum_ditugaskan' => Tab::make('Belum Ditugaskan')
                ->icon('heroicon-m-clock')
                ->badge(RincianAnggaranBelanja::where('tahun_anggaran', (int) $selectedTahun)->whereNull('bidang_id')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNull('bidang_id')),
        ];

        $bidangs = Bidang::query()->orderBy('nama')->get();
        foreach ($bidangs as $bidang) {
            $icon = match ($bidang->slug) {
                'sekretariat' => 'heroicon-m-building-office',
                'pemasaran-pariwisata', 'bidang-pemasaran-pariwisata' => 'heroicon-m-megaphone',
                'pengembangan-destinasi-pariwisata' => 'heroicon-m-map-pin',
                'pemuda-dan-olahraga' => 'heroicon-m-trophy',
                default => 'heroicon-m-briefcase',
            };

            $count = RincianAnggaranBelanja::where('tahun_anggaran', (int) $selectedTahun)
                ->where('bidang_id', $bidang->id)
                ->count();

            $tabs[$bidang->slug ?: 'bidang_' . $bidang->id] = Tab::make($bidang->nama)
                ->icon($icon)
                ->badge($count)
                ->modifyQueryUsing(fn(Builder $query) => $query->where('bidang_id', $bidang->id));
        }

        return $tabs;
    }
}
