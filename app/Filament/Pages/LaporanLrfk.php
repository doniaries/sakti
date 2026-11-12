<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Bidang;
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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class LaporanLrfk extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan LRFK';

    protected static ?string $title = 'Laporan Realisasi Fisik dan Keuangan (LRFK)';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.laporan-lrfk';

    public int $tahun;

    public int $bulan;

    public ?int $bidangId = null;

    public ?int $programId = null;

    public ?int $kegiatanId = null;

    public string $activeTab = 'lampiran1';

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
        $defaultTahun = \App\Models\PengaturanAplikasi::getTahunAnggaranAktif();
        $this->tahun = (int) (request()->query('tahun') ?: $defaultTahun);
        $this->bulan = (int) (request()->query('bulan') ?: date('n'));
        $this->bidangId = request()->query('bidang_id') ? (int) request()->query('bidang_id') : null;
        $this->programId = request()->query('program_id') ? (int) request()->query('program_id') : null;
        $this->kegiatanId = request()->query('kegiatan_id') ? (int) request()->query('kegiatan_id') : null;
    }

    public function updatedBidangId(): void
    {
        $this->programId = null;
        $this->kegiatanId = null;
    }

    public function updatedProgramId(): void
    {
        $this->kegiatanId = null;
    }

    public function resetFilter(): void
    {
        $this->bidangId = null;
        $this->programId = null;
        $this->kegiatanId = null;
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak_lrfk')
                ->label('Pratinjau & Cetak LRFK (PDF)')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->modalHeading('Pratinjau Dokumen LRFK (Kertas F4 Landscape)')
                ->modalWidth(Width::Screen)
                ->form([
                    Grid::make(3)->schema([
                        Select::make('modal_bulan')
                            ->label('Periode s/d Bulan')
                            ->options($this->getDaftarBulan())
                            ->default(fn () => $this->bulan)
                            ->live()
                            ->required(),

                        Select::make('modal_penandatangan')
                            ->label('Penandatangan Dokumen')
                            ->options([
                                'sekretaris' => 'a.n. Kepala Dinas (Sekretaris)',
                                'kepala_dinas' => 'Kepala Dinas Langsung',
                            ])
                            ->default('sekretaris')
                            ->live()
                            ->required(),

                        Select::make('modal_tipe')
                            ->label('Pilihan Lembar Laporan')
                            ->options([
                                'lengkap' => 'Buku LRFK Lengkap (Semua Halaman 1, 2, 3)',
                                'lampiran1' => 'Halaman 1: Laporan Realisasi Belanja',
                                'kemajuan' => 'Halaman 2: Laporan Kemajuan Pekerjaan',
                                'matriks' => 'Halaman 3: Laporan Realisasi Fisik dan Keuangan (LRFK)',
                            ])
                            ->default(fn () => $this->activeTab === 'lampiran1' ? 'lengkap' : $this->activeTab)
                            ->live()
                            ->required(),
                    ]),

                    View::make('filament.components.pdf-preview-embed')
                        ->viewData([
                            'tahun' => $this->tahun,
                            'jenis' => 'lrfk',
                        ]),
                ])
                ->extraModalFooterActions([
                    Action::make('modal_download_pdf')
                        ->label('Unduh Dokumen PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (array $data) {
                            $bulanPilihan = (int) ($data['modal_bulan'] ?? $this->bulan);
                            $penandatanganPilihan = (string) ($data['modal_penandatangan'] ?? 'sekretaris');
                            $tipePilihan = (string) ($data['modal_tipe'] ?? 'lengkap');

                            $url = route('admin.laporan.lrfk.cetak', [
                                'tahun' => $this->tahun,
                                'bulan' => $bulanPilihan,
                                'penandatangan' => $penandatanganPilihan,
                                'tipe' => $tipePilihan,
                                'bidang_id' => $this->bidangId,
                                'program_id' => $this->programId,
                                'kegiatan_id' => $this->kegiatanId,
                                'download' => 1,
                            ]);

                            $this->js("window.location.href = '{$url}'");
                        }),
                ])
                ->modalSubmitActionLabel('Buka di Tab Penuh')
                ->modalCancelActionLabel('Tutup')
                ->action(function (array $data) {
                    $bulanPilihan = (int) ($data['modal_bulan'] ?? $this->bulan);
                    $penandatanganPilihan = (string) ($data['modal_penandatangan'] ?? 'sekretaris');
                    $tipePilihan = (string) ($data['modal_tipe'] ?? 'lengkap');

                    // Sinkronkan state halaman agar tampilan tabel di layar ikut sinkron seketika
                    $this->bulan = $bulanPilihan;
                    if (in_array($tipePilihan, ['matriks', 'lampiran1', 'kemajuan'], true)) {
                        $this->activeTab = $tipePilihan;
                    }

                    $url = route('admin.laporan.lrfk.cetak', [
                        'tahun' => $this->tahun,
                        'bulan' => $bulanPilihan,
                        'penandatangan' => $penandatanganPilihan,
                        'tipe' => $tipePilihan,
                        'bidang_id' => $this->bidangId,
                        'program_id' => $this->programId,
                        'kegiatan_id' => $this->kegiatanId,
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
            'matriks' => $service->getMatriksLrfkKegiatan($this->tahun, $this->bulan, $this->bidangId, $this->programId, $this->kegiatanId),
            'lampiran1' => $service->getLampiran1JenisBelanja($this->tahun, $this->bulan, $this->bidangId, $this->programId, $this->kegiatanId),
            'kemajuan' => $service->getKemajuanPekerjaanFisik($this->tahun, $this->bulan, $this->bidangId, $this->programId, $this->kegiatanId),
            'daftarBulan' => $this->getDaftarBulan(),
            'namaBulanAktif' => $this->getDaftarBulan()[$this->bulan] ?? '',
            'daftarBidang' => $this->getDaftarBidang(),
            'daftarProgram' => $this->getDaftarProgram(),
            'daftarKegiatan' => $this->getDaftarKegiatan(),
        ];
    }
}
