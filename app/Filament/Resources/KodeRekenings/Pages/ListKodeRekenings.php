<?php

namespace App\Filament\Resources\KodeRekenings\Pages;

use App\Filament\Resources\KodeRekenings\KodeRekeningResource;
use App\Models\KodeRekening;
use App\Services\KodeRekening\KodeRekeningImportService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListKodeRekenings extends ListRecords
{
    protected static string $resource = KodeRekeningResource::class;

    public function getDefaultActiveTab(): string|int|null
    {
        return 'sub_rincian';
    }

    public function getTabs(): array
    {
        return [
            'sub_rincian' => Tab::make('Sub Rincian Obyek')
                ->badge(KodeRekening::query()->whereRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', ''))) = 5")->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', ''))) = 5")),

            'rincian' => Tab::make('Rincian Obyek')
                ->badge(KodeRekening::query()->whereRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', ''))) = 4")->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', ''))) = 4")),

            'obyek' => Tab::make('Obyek')
                ->badge(KodeRekening::query()->whereRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', ''))) = 3")->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', ''))) = 3")),

            'jenis' => Tab::make('Jenis')
                ->badge(KodeRekening::query()->whereRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', ''))) = 2")->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', ''))) = 2")),

            'kelompok_akun' => Tab::make('Kelompok & Akun')
                ->badge(KodeRekening::query()->whereRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', ''))) <= 1")->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', ''))) <= 1")),

            'semua' => Tab::make('Semua')
                ->badge(KodeRekening::count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Rekening')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),

            Action::make('import_data')
                ->label('Import CSV / SQL')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Data Kode Rekening Belanja')
                ->modalDescription('Unggah berkas kode rekening dalam format .CSV atau .SQL hasil ekstraksi Kepmendagri / SIPD RI.')
                ->modalSubmitActionLabel('Mulai Import Sekarang')
                ->schema([
                    FileUpload::make('file')
                        ->label('Pilih Berkas (.CSV atau .SQL)')
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/sql',
                            'text/x-sql',
                            'application/octet-stream',
                            'application/vnd.ms-excel',
                        ])
                        ->helperText('Mendukung file .csv (dengan kolom kode_lengkap, uraian) atau file .sql yang memuat perintah INSERT INTO.')
                        ->required(),

                    Radio::make('mode')
                        ->label('Metode Penyimpanan')
                        ->options([
                            'update_or_create' => 'Perbarui data yang sudah ada & Tambahkan data baru (Disarankan)',
                            'replace_all' => 'Kosongkan tabel terlebih dahulu lalu import ulang seluruhnya',
                        ])
                        ->default('update_or_create')
                        ->required(),
                ])
                ->action(function (array $data, KodeRekeningImportService $service): void {
                    try {
                        $result = $service->import($data['file'], $data['mode']);
                        $total = number_format($result['total'], 0, ',', '.');
                        $tipe = strtoupper($result['type']);

                        Notification::make()
                            ->title('Import Kode Rekening Berhasil!')
                            ->body("Berhasil memproses berkas {$tipe}. Saat ini total tersimpan {$total} kode rekening belanja.")
                            ->success()
                            ->persistent()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Import Gagal')
                            ->body('Terjadi kesalahan saat memproses data: ' . $e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),

            ActionGroup::make([
                Action::make('download_csv')
                    ->label('Unduh Master CSV (3.283 Rekening)')
                    ->icon('heroicon-o-table-cells')
                    ->url('/downloads/kode_rekening_belanja.csv')
                    ->openUrlInNewTab(),

                Action::make('download_sql')
                    ->label('Unduh Master SQL (Siap Impor)')
                    ->icon('heroicon-o-circle-stack')
                    ->url('/downloads/kode_rekening_belanja.sql')
                    ->openUrlInNewTab(),
            ])
                ->label('Unduh Data Rekening')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray'),
        ];
    }
}
