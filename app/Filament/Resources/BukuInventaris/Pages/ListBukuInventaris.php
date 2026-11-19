<?php

declare(strict_types=1);

namespace App\Filament\Resources\BukuInventaris\Pages;

use App\Filament\Resources\BukuInventaris\BukuInventarisResource;
use App\Filament\Resources\BukuInventaris\Widgets\BukuInventarisOverviewWidget;
use App\Models\BukuInventaris;
use App\Services\BukuInventarisPdfImporter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class ListBukuInventaris extends ListRecords
{
    protected static string $resource = BukuInventarisResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            BukuInventarisOverviewWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Aset')
                ->badge(BukuInventaris::count()),

            'baik' => Tab::make('Kondisi Baik')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('keadaan_barang', 'Baik'))
                ->badge(BukuInventaris::where('keadaan_barang', 'Baik')->count())
                ->badgeColor('success'),

            'kurang_baik' => Tab::make('Kurang Baik')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('keadaan_barang', 'Kurang Baik'))
                ->badge(BukuInventaris::where('keadaan_barang', 'Kurang Baik')->count())
                ->badgeColor('warning'),

            'rusak_berat' => Tab::make('Rusak Berat')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('keadaan_barang', 'Rusak Berat'))
                ->badge(BukuInventaris::where('keadaan_barang', 'Rusak Berat')->count())
                ->badgeColor('danger'),

            'gedung' => Tab::make('Gedung & Bangunan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kode_barang', 'like', '01%'))
                ->badge(BukuInventaris::where('kode_barang', 'like', '01%')->count())
                ->badgeColor('info'),

            'peralatan' => Tab::make('Peralatan & Mesin')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kode_barang', 'like', '02%'))
                ->badge(BukuInventaris::where('kode_barang', 'like', '02%')->count())
                ->badgeColor('primary'),

            'sampah' => Tab::make('Sampah / Terhapus')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge(BukuInventaris::onlyTrashed()->count())
                ->badgeColor('danger'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak_laporan')
                ->label('Cetak / Pratinjau Buku Inventaris')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->modalHeading('Pratinjau Buku Inventaris Gabungan (KIB)')
                ->modalWidth(Width::SevenExtraLarge)
                ->modalContent(fn (): HtmlString => new HtmlString(
                    '<iframe src="' . route('admin.buku-inventaris.cetak', ['preview' => 1]) . '" class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white" style="height: 78vh;"></iframe>'
                ))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->extraModalFooterActions([
                    Action::make('download_pdf')
                        ->label('Unduh PDF Resmi')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('danger')
                        ->url(fn (): string => route('admin.buku-inventaris.cetak'))
                        ->openUrlInNewTab(),
                ]),

            Action::make('import_pdf_bkad')
                ->label('Update dari PDF BKAD')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->visible(function (): bool {
                    /** @var \App\Models\User|null $user */
                    $user = auth()->user();

                    return $user?->canManageAset() ?? false;
                })
                ->modalHeading('Update Data Aset dari PDF BKAD')
                ->modalDescription('Unggah file PDF Buku Inventaris Gabungan resmi dari sistem aset BKAD untuk memperbarui atau menyinkronkan data inventaris.')
                ->form([
                    FileUpload::make('pdf_file')
                        ->label('File PDF BKAD (Buku Inventaris Gabungan)')
                        ->acceptedFileTypes(['application/pdf'])
                        ->disk('local')
                        ->directory('temp-imports')
                        ->required(),
                    Radio::make('mode')
                        ->label('Mode Sinkronisasi')
                        ->options([
                            'upsert' => 'Update & Tambah Data Baru (Pertahankan data yang tidak berubah)',
                            'replace' => 'Ganti Total (Hapus sementara data lama, ganti 100% sesuai PDF)',
                        ])
                        ->default('upsert')
                        ->required(),
                ])
                ->action(function (array $data, BukuInventarisPdfImporter $importer): void {
                    $relativePath = $data['pdf_file'];
                    $fullPath = Storage::disk('local')->path($relativePath);

                    try {
                        $result = $importer->import($fullPath, $data['mode'] ?? 'upsert');

                        Notification::make()
                            ->title('Sinkronisasi PDF BKAD Berhasil')
                            ->body(sprintf(
                                'Terbaca: %d baris | Ditambahkan: %d | Diperbarui: %d | Total Nilai: Rp %s',
                                $result['total_parsed'],
                                $result['inserted'],
                                $result['updated'],
                                number_format($result['total_harga'], 2, ',', '.')
                            ))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Memproses PDF BKAD')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        Storage::disk('local')->delete($relativePath);
                    }
                }),

            CreateAction::make()
                ->label('Tambah Aset')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }
}
