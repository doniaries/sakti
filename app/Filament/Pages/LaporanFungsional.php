<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\RincianAnggaranBelanja;
use App\Services\Laporan\LrfkCalculationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class LaporanFungsional extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Laporan Fungsional';

    protected static ?string $title = 'Laporan Pertanggungjawaban Fungsional (LPJ)';

    protected static ?int $navigationSort = 4;

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
        $defaultTahun = RincianAnggaranBelanja::max('tahun_anggaran') ?? (int) date('Y');
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
            Action::make('cetak_lpj')
                ->label('Cetak LPJ Fungsional (PDF)')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(fn (): string => route('admin.laporan.fungsional.cetak', [
                    'tahun' => $this->tahun,
                    'bulan' => $this->bulan,
                ]))
                ->openUrlInNewTab(),
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

