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

    public ?int $bidangId = null;

    public ?int $programId = null;

    public ?int $kegiatanId = null;

    public ?int $subKegiatanId = null;

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
        $this->bidangId = request()->query('bidang_id') ? (int) request()->query('bidang_id') : null;
        $this->programId = request()->query('program_id') ? (int) request()->query('program_id') : null;
        $this->kegiatanId = request()->query('kegiatan_id') ? (int) request()->query('kegiatan_id') : null;
        $this->subKegiatanId = request()->query('sub_kegiatan_id') ? (int) request()->query('sub_kegiatan_id') : null;
    }

    public function updatedBidangId(): void
    {
        $this->programId = null;
        $this->kegiatanId = null;
        $this->subKegiatanId = null;
    }

    public function updatedProgramId(): void
    {
        $this->kegiatanId = null;
        $this->subKegiatanId = null;
    }

    public function updatedKegiatanId(): void
    {
        $this->subKegiatanId = null;
    }

    public function resetFilter(): void
    {
        $this->bidangId = null;
        $this->programId = null;
        $this->kegiatanId = null;
        $this->subKegiatanId = null;
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

    public function getDaftarBidang(): array
    {
        return \App\Models\Bidang::orderBy('nama')->pluck('nama', 'id')->all();
    }

    public function getDaftarProgram(): array
    {
        return \App\Models\MasterProgram::query()
            ->when($this->bidangId, fn ($q) => $q->whereHas('kegiatans', fn ($kq) => $kq->where('bidang_id', $this->bidangId)))
            ->orderBy('kode_program')
            ->get()
            ->mapWithKeys(fn ($p) => [$p->id => "{$p->kode_program} - {$p->nama_program}"])
            ->all();
    }

    public function getDaftarKegiatan(): array
    {
        return \App\Models\MasterKegiatan::query()
            ->when($this->bidangId, fn ($q) => $q->where('bidang_id', $this->bidangId))
            ->when($this->programId, fn ($q) => $q->where('program_id', $this->programId))
            ->orderBy('kode_kegiatan')
            ->get()
            ->mapWithKeys(fn ($k) => [$k->id => "{$k->kode_kegiatan} - {$k->nama_kegiatan}"])
            ->all();
    }

    public function getDaftarSubKegiatan(): array
    {
        return \App\Models\MasterSubKegiatan::query()
            ->when($this->kegiatanId, fn ($q) => $q->where('kegiatan_id', $this->kegiatanId))
            ->when(! $this->kegiatanId && $this->bidangId, fn ($q) => $q->whereHas('kegiatan', fn ($kq) => $kq->where('bidang_id', $this->bidangId)))
            ->when(! $this->kegiatanId && $this->programId, fn ($q) => $q->whereHas('kegiatan', fn ($kq) => $kq->where('program_id', $this->programId)))
            ->orderBy('kode_sub_kegiatan')
            ->get()
            ->mapWithKeys(fn ($s) => [$s->id => "{$s->kode_sub_kegiatan} - {$s->nama_sub_kegiatan}"])
            ->all();
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
                ->extraModalFooterActions([
                    Action::make('modal_download_pdf')
                        ->label('Unduh Dokumen PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (array $data) {
                            $bulanPilihan = (int) ($data['modal_bulan'] ?? $this->bulan);
                            $this->bulan = $bulanPilihan;

                            $url = route('admin.laporan.fungsional.cetak', [
                                'tahun' => $this->tahun,
                                'bulan' => $bulanPilihan,
                                'bidang_id' => $this->bidangId,
                                'program_id' => $this->programId,
                                'kegiatan_id' => $this->kegiatanId,
                                'sub_kegiatan_id' => $this->subKegiatanId,
                                'download' => 1,
                            ]);

                            $this->js("window.location.href = '{$url}'");
                        }),
                ])
                ->modalSubmitActionLabel('Buka di Tab Penuh')
                ->modalCancelActionLabel('Tutup')
                ->action(function (array $data) {
                    $bulanPilihan = (int) ($data['modal_bulan'] ?? $this->bulan);
                    $this->bulan = $bulanPilihan;

                    $url = route('admin.laporan.fungsional.cetak', [
                        'tahun' => $this->tahun,
                        'bulan' => $bulanPilihan,
                        'bidang_id' => $this->bidangId,
                        'program_id' => $this->programId,
                        'kegiatan_id' => $this->kegiatanId,
                        'sub_kegiatan_id' => $this->subKegiatanId,
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
            'data' => $service->getLpjFungsional(
                $this->tahun,
                $this->bulan,
                $this->bidangId,
                $this->programId,
                $this->kegiatanId,
                $this->subKegiatanId
            ),
            'daftarBulan' => $this->getDaftarBulan(),
            'namaBulanAktif' => $this->getDaftarBulan()[$this->bulan] ?? '',
            'daftarBidang' => $this->getDaftarBidang(),
            'daftarProgram' => $this->getDaftarProgram(),
            'daftarKegiatan' => $this->getDaftarKegiatan(),
            'daftarSubKegiatan' => $this->getDaftarSubKegiatan(),
        ];
    }
}
