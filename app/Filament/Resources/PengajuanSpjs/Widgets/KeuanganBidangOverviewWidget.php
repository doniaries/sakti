<?php

declare(strict_types=1);

namespace App\Filament\Resources\PengajuanSpjs\Widgets;

use App\Filament\Resources\KodeRekenings\KodeRekeningResource;
use App\Filament\Resources\MasterBarangs\MasterBarangResource;
use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\MasterPrograms\MasterProgramResource;
use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\MasterTokos\MasterTokoResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Filament\Resources\StokBarangs\StokBarangResource;
use App\Models\Bidang;
use App\Models\MasterKegiatan;
use App\Models\MasterSubKegiatan;
use App\Models\PengajuanSpj;
use App\Models\StokBarang;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class KeuanganBidangOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected string $view = 'filament.widgets.keuangan-bidang-overview-widget';

    protected int | string | array $columnSpan = 'full';

    public ?int $selectedBidangId = null;

    public ?int $selectedTahun = null;

    public ?int $selectedBulan = null;

    public ?string $selectedTanggal = null;

    public function mount(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $queryBidang = request()->query('bidang')
            ?? data_get(request()->query('filters'), 'bidang_id.value')
            ?? data_get(request()->query('tableFilters'), 'bidang_id.value');

        $queryBulan = request()->query('bulan');
        $queryTanggal = request()->query('tanggal');

        if (request()->is('livewire/*') && request()->header('Referer')) {
            $refererQuery = [];
            parse_str(parse_url(request()->header('Referer'), PHP_URL_QUERY) ?? '', $refererQuery);
            if (! request()->has('bidang')) {
                $queryBidang = $queryBidang ?? ($refererQuery['bidang']
                    ?? data_get($refererQuery, 'filters.bidang_id.value')
                    ?? data_get($refererQuery, 'tableFilters.bidang_id.value'));
            }
            if (! request()->has('bulan')) {
                $queryBulan = $queryBulan ?? ($refererQuery['bulan'] ?? null);
            }
            if (! request()->has('tanggal')) {
                $queryTanggal = $queryTanggal ?? ($refererQuery['tanggal'] ?? null);
            }
        }

        if (filled($queryBidang)) {
            $this->selectedBidangId = (int) $queryBidang;
        } elseif ($user !== null && filled($user->bidang_id) && ! $this->canChooseBidang()) {
            $this->selectedBidangId = (int) $user->bidang_id;
        } else {
            $this->selectedBidangId = null;
        }

        if (filled($this->selectedTanggal)) {
            $this->selectedBulan = (int) date('n', strtotime($this->selectedTanggal));
        } else {
            $this->selectedBulan = match (true) {
                $queryBulan === 'all' => null,
                filled($queryBulan) => (int) $queryBulan,
                default => (int) date('n'),
            };
        }
        $this->selectedTanggal = filled($queryTanggal) ? (string) $queryTanggal : null;

        $this->selectedTahun = \App\Models\PengaturanAplikasi::getTahunAnggaranAktif();
    }

    public function getCleanPageUrl(): string
    {
        $referer = (string) request()->header('Referer', '');
        $currentPath = (string) request()->path();

        $checkString = str_starts_with($currentPath, 'livewire/') ? $referer : $currentPath;

        if (str_contains($checkString, 'pengajuan-spjs')) {
            return PengajuanSpjResource::getUrl('index');
        }

        if (str_contains($checkString, 'master-programs')) {
            return MasterProgramResource::getUrl('index');
        }

        if (str_contains($checkString, 'master-kegiatans')) {
            return MasterKegiatanResource::getUrl('index');
        }

        if (str_contains($checkString, 'master-sub-kegiatans')) {
            return MasterSubKegiatanResource::getUrl('index');
        }

        if (str_contains($checkString, 'stok-barangs')) {
            return StokBarangResource::getUrl('index');
        }

        if (str_contains($checkString, 'master-barangs')) {
            return MasterBarangResource::getUrl('index');
        }

        if (str_contains($checkString, 'kode-rekenings')) {
            return KodeRekeningResource::getUrl('index');
        }

        if (str_contains($checkString, 'master-tokos')) {
            return MasterTokoResource::getUrl('index');
        }

        return PengajuanSpjResource::getUrl('index');
    }

    public static function canView(): bool
    {
        return Auth::check();
    }

    protected function getColumns(): int
    {
        return 4;
    }

    public function canChooseBidang(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        if ($user->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara'])) {
            return true;
        }

        if (blank($user->bidang_id)) {
            return true;
        }

        return $user->can('viewAny', Bidang::class);
    }

    public function getDaftarBidang(): Collection
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user !== null && $this->canChooseBidang()) {
            return Bidang::with('kepala:id,name,nip')->orderBy('nama')->get();
        }

        if ($user !== null && filled($user->bidang_id)) {
            return Bidang::with('kepala:id,name,nip')->whereKey($user->bidang_id)->get();
        }

        return collect();
    }

    public function getActiveBidang(): ?Bidang
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $this->canChooseBidang() && $user !== null && filled($user->bidang_id)) {
            return Bidang::with('kepala:id,name,nip')->find($user->bidang_id);
        }

        if ($this->selectedBidangId !== null && $this->selectedBidangId > 0) {
            $bidang = Bidang::with('kepala:id,name,nip')->find($this->selectedBidangId);
            if ($bidang !== null) {
                return $bidang;
            }
        }

        return null;
    }

    public function getHeading(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return null;
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

    protected function getStats(): array
    {
        $bidang = $this->getActiveBidang();
        $bidangId = $bidang?->id;
        $tahun = $this->selectedTahun ?? (int) date('Y');

        // 1. Total Anggaran (Pagu Kegiatan Bidang / Semua pada tahun terpilih)
        $totalAnggaranQuery = MasterKegiatan::query()->where('tahun_anggaran', $tahun);
        if ($bidangId !== null) {
            $totalAnggaranQuery->where('bidang_id', $bidangId);
        }
        $totalAnggaran = (float) $totalAnggaranQuery->sum('pagu_anggaran');

        // 2. Total Kegiatan & Sub Kegiatan pada tahun terpilih
        $totalKegiatanQuery = MasterKegiatan::query()->where('tahun_anggaran', $tahun);
        $totalSubKegiatanQuery = MasterSubKegiatan::query()->where('tahun_anggaran', $tahun);
        if ($bidangId !== null) {
            $totalKegiatanQuery->where('bidang_id', $bidangId);
            $totalSubKegiatanQuery->whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $bidangId));
        }
        $totalKegiatan = $totalKegiatanQuery->count();
        $totalSubKegiatan = $totalSubKegiatanQuery->count();

        // 3. Total Uang Sudah Di-SPJ-kan (Realisasi Belanja)
        $validStatuses = [
            'dicairkan',
            'disetujui',
            'menunggu_verifikasi_bendahara',
            'menunggu_persetujuan_pa',
            'menunggu_verifikasi_ppk',
            'menunggu_verifikasi_pptk',
        ];

        $spjQuery = PengajuanSpj::query()->where('tahun_anggaran', $tahun);
        if ($bidangId !== null) {
            $spjQuery->where('bidang_id', $bidangId);
        }
        if ($this->selectedBulan !== null) {
            $spjQuery->whereMonth('tanggal_pengajuan', $this->selectedBulan);
        }
        if ($this->selectedTanggal !== null) {
            $spjQuery->whereDate('tanggal_pengajuan', $this->selectedTanggal);
        }

        $totalSudahSpj = (float) (clone $spjQuery)->whereIn('status', $validStatuses)->sum('total_nilai_realisasi');
        $totalSpjCair = (clone $spjQuery)->where('status', 'dicairkan')->count();
        $totalSemuaSpj = (clone $spjQuery)->count();

        // 4. Sisa Anggaran (Pagu - Realisasi SPJ)
        $sisaAnggaran = max(0, $totalAnggaran - $totalSudahSpj);
        $persenSisa = $totalAnggaran > 0 ? round(($sisaAnggaran / $totalAnggaran) * 100, 1) : 0;

        $filterParams = array_filter([
            'bidang' => $bidangId,
            'tahun' => $tahun,
            'bulan' => $this->selectedBulan,
            'tanggal' => $this->selectedTanggal,
        ]);

        return [
            Stat::make('Total Anggaran', 'Rp ' . number_format($totalAnggaran, 0, ',', '.'))
                ->description("Alokasi pagu TA {$tahun}")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary')
                ->url(MasterKegiatanResource::getUrl('index', $filterParams)),

            Stat::make('Total Kegiatan', $totalKegiatan . ' Kegiatan')
                ->description($totalSubKegiatan . ' Sub-Kegiatan aktif')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info')
                ->url(MasterKegiatanResource::getUrl('index', $filterParams)),

            Stat::make('Realisasi SPJ', 'Rp ' . number_format($totalSudahSpj, 0, ',', '.'))
                ->description("{$totalSpjCair} dari {$totalSemuaSpj} SPJ dicairkan")
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color($totalSudahSpj > 0 ? 'warning' : 'gray')
                ->url(PengajuanSpjResource::getUrl('index', $filterParams)),

            Stat::make('Sisa Anggaran', 'Rp ' . number_format($sisaAnggaran, 0, ',', '.'))
                ->description($totalAnggaran > 0 ? "{$persenSisa}% pagu tersedia" : 'Pagu belum dialokasikan')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color($sisaAnggaran > 0 ? 'success' : 'danger')
                ->url(PengajuanSpjResource::getUrl('index', $filterParams)),
        ];
    }
}
