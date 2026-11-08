<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Bidang;
use App\Models\RincianAnggaranBelanja;
use App\Services\Laporan\LrfkCalculationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class LaporanLrfk extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Laporan LRFK';

    protected static ?string $title = 'Laporan Realisasi Fisik dan Keuangan (LRFK)';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.laporan-lrfk';

    public int $tahun;

    public int $bulan;

    public ?int $bidangId = null;

    public string $activeTab = 'matriks';

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user !== null && $user->hasAnyRole([
            'super_admin',
            'admin',
            'pa',
            'ppk',
            'pptk',
            'jf',
            'bendahara',
        ]);
    }

    public function mount(): void
    {
        $defaultTahun = RincianAnggaranBelanja::max('tahun_anggaran') ?? (int) date('Y');
        $this->tahun = (int) (request()->query('tahun') ?: $defaultTahun);
        $this->bulan = (int) (request()->query('bulan') ?: date('n'));

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user && $user->hasRole('jf') && ! $user->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara'])) {
            $this->bidangId = $user->bidang_id;
        } else {
            $this->bidangId = request()->query('bidang') ? (int) request()->query('bidang') : null;
        }
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

    public function getDaftarBidang(): Collection
    {
        return Bidang::query()->orderBy('nama')->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak_tab')
                ->label(fn () => match ($this->activeTab) {
                    'lampiran1' => 'Cetak Lampiran 1 (PDF)',
                    'kemajuan' => 'Cetak Kemajuan Fisik (PDF)',
                    default => 'Cetak Matriks LRFK (PDF)',
                })
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(fn (): string => route('admin.laporan.lrfk.cetak', [
                    'tahun' => $this->tahun,
                    'bulan' => $this->bulan,
                    'bidang' => $this->bidangId,
                    'tipe' => $this->activeTab,
                ]))
                ->openUrlInNewTab(),

            Action::make('cetak_lengkap')
                ->label('Cetak Buku LRFK Lengkap (PDF)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(fn (): string => route('admin.laporan.lrfk.cetak', [
                    'tahun' => $this->tahun,
                    'bulan' => $this->bulan,
                    'bidang' => $this->bidangId,
                    'tipe' => 'lengkap',
                ]))
                ->openUrlInNewTab(),
        ];
    }

    protected function getViewData(): array
    {
        /** @var LrfkCalculationService $service */
        $service = app(LrfkCalculationService::class);

        return [
            'matriks' => $service->getMatriksLrfkKegiatan($this->tahun, $this->bulan, $this->bidangId),
            'lampiran1' => $service->getLampiran1JenisBelanja($this->tahun, $this->bulan, $this->bidangId),
            'kemajuan' => $service->getKemajuanPekerjaanFisik($this->tahun, $this->bulan, $this->bidangId),
            'daftarBulan' => $this->getDaftarBulan(),
            'daftarBidang' => $this->getDaftarBidang(),
            'namaBulanAktif' => $this->getDaftarBulan()[$this->bulan] ?? '',
        ];
    }
}

