<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\RincianAnggaranBelanja;
use App\Services\Laporan\LrfkCalculationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\View;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class LaporanFungsional extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Fungsional';

    protected static ?string $title = 'Laporan Pertanggungjawaban Fungsional (LPJ)';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.laporan-fungsional';

    public int $tahun;

    public int $bulan;

    public string $activeTab = 'rekening';

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user !== null && $user->hasAnyRole([
            'super_admin',
            'admin',
            'pa',
            'ppk',
            'bendahara',
        ]);
    }

    public function mount(): void
    {
        $defaultTahun = \App\Models\PengaturanAplikasi::getTahunAnggaranAktif();
        $this->tahun = (int) (request()->query('tahun') ?: $defaultTahun);
        $this->bulan = (int) (request()->query('bulan') ?: date('n'));
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getDaftarBulan(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview_cetak_fungsional')
                ->label('Pratinjau & Cetak LPJ (PDF)')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->modalHeading('Pratinjau Dokumen LPJ Fungsional (Kertas F4 Landscape)')
                ->modalDescription('Periksa seluruh halaman laporan pertanggungjawaban bendahara sebelum dicetak atau diunduh:')
                ->modalWidth(Width::Screen)
                ->form([
                    Grid::make(1)->schema([
                        Select::make('modal_bulan')
                            ->label('Filter Periode s/d Bulan')
                            ->options($this->getDaftarBulan())
                            ->default(fn () => $this->bulan)
                            ->live()
                            ->required(),
                    ]),

                    View::make('filament.components.pdf-preview-embed')
                        ->viewData([
                            'tahun' => $this->tahun,
                            'jenis' => 'fungsional',
                        ]),
                ])
                ->modalSubmitActionLabel('Buka di Tab Penuh')
                ->modalCancelActionLabel('Tutup')
                ->action(function (array $data) {
                    $bulanPilihan = (int) ($data['modal_bulan'] ?? $this->bulan);

                    // Update state halaman jika user memilih bulan berbeda
                    $this->bulan = $bulanPilihan;

                    $url = route('admin.laporan.fungsional.cetak', [
                        'tahun' => $this->tahun,
                        'bulan' => $bulanPilihan,
                    ]);

                    $this->js("window.open('{$url}', '_blank')");
                }),
        ];
    }

    protected function getViewData(): array
    {
        /** @var LrfkCalculationService $service */
        $service = app(LrfkCalculationService::class);

        return [
            'data' => $service->getLpjFungsional($this->tahun, $this->bulan),
            'daftarBulan' => $this->getDaftarBulan(),
            'namaBulanAktif' => $this->getDaftarBulan()[$this->bulan] ?? '',
        ];
    }
}
