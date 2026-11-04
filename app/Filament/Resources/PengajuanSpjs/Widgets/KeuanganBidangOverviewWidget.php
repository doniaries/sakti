<?php

declare(strict_types=1);

namespace App\Filament\Resources\PengajuanSpjs\Widgets;

use App\Filament\Resources\MasterBarangs\MasterBarangResource;
use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
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

    public function mount(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $queryBidang = request()->query('bidang')
            ?? data_get(request()->query('filters'), 'bidang_id.value')
            ?? data_get(request()->query('tableFilters'), 'bidang_id.value');

        if (blank($queryBidang) && request()->header('Referer')) {
            $refererQuery = [];
            parse_str(parse_url(request()->header('Referer'), PHP_URL_QUERY) ?? '', $refererQuery);
            $queryBidang = $refererQuery['bidang']
                ?? data_get($refererQuery, 'filters.bidang_id.value')
                ?? data_get($refererQuery, 'tableFilters.bidang_id.value');
        }

        if (filled($queryBidang)) {
            $this->selectedBidangId = (int) $queryBidang;
        } elseif ($user !== null && filled($user->bidang_id) && ! $this->canChooseBidang()) {
            $this->selectedBidangId = (int) $user->bidang_id;
        } else {
            $this->selectedBidangId = null;
        }
    }

    public function getCleanPageUrl(): string
    {
        $referer = (string) request()->header('Referer', '');
        $currentPath = (string) request()->path();

        $checkString = str_starts_with($currentPath, 'livewire/') ? $referer : $currentPath;

        if (str_contains($checkString, 'pengajuan-spjs')) {
            return PengajuanSpjResource::getUrl('index');
        }

        if (str_contains($checkString, 'master-kegiatans')) {
            return MasterKegiatanResource::getUrl('index');
        }

        if (str_contains($checkString, 'master-sub-kegiatans')) {
            return \App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource::getUrl('index');
        }

        if (str_contains($checkString, 'master-barangs')) {
            return MasterBarangResource::getUrl('index');
        }

        if (str_contains($checkString, 'kode-rekenings')) {
            return \App\Filament\Resources\KodeRekenings\KodeRekeningResource::getUrl('index');
        }

        if (str_contains($checkString, 'master-tokos')) {
            return \App\Filament\Resources\MasterTokos\MasterTokoResource::getUrl('index');
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

    public function selectBidang(?int $id = null): void
    {
        if ($this->canChooseBidang()) {
            $this->selectedBidangId = $id;

            $baseUrl = $this->getCleanPageUrl();
            $params = ($id !== null && $id > 0) ? ['bidang' => $id] : [];

            $targetUrl = ! empty($params) ? $baseUrl . '?' . http_build_query($params) : $baseUrl;

            $this->redirect($targetUrl, navigate: true);
        }
    }

    public function canChooseBidang(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        // Pengguna tanpa bidang_id (Admin / Kadis / PPK / Bendahara) atau Super Admin
        if (blank($user->bidang_id)) {
            return true;
        }

        $anggaranRole = $user->anggaran_role instanceof \App\Enums\AnggaranRole
            ? $user->anggaran_role->value
            : $user->anggaran_role;

        return in_array($anggaranRole, ['PA', 'PPK', 'BENDAHARA'], true)
            || $user->hasRole(['super_admin', 'admin']);
    }

    public function getDaftarBidang(): Collection
    {
        return Bidang::with('kepala:id,name,nip')->get();
    }

    public function getActiveBidang(): ?Bidang
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Jika user dibatasi ke bidang tertentu dan bukan admin
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
        $bidang = $this->getActiveBidang();
        $namaBidang = $bidang?->nama ?? 'Semua Bidang';

        return "Pedoman Belanja & Keuangan: {$namaBidang}";
    }

    public function getDescription(): ?string
    {
        $bidang = $this->getActiveBidang();
        if ($bidang !== null) {
            $pptk = $bidang->kepala?->name ? " (PPTK: {$bidang->kepala->name})" : '';
            return "Pedoman alokasi anggaran, kegiatan, stok kritis barang, dan realisasi SPJ{$pptk} sebelum belanja";
        }

        return 'Pedoman alokasi anggaran, kegiatan, stok kritis barang, dan realisasi SPJ seluruh bidang dinas sebelum belanja';
    }

    protected function getStats(): array
    {
        $bidang = $this->getActiveBidang();
        $bidangId = $bidang?->id;

        // 1. Total Anggaran (Pagu Kegiatan Bidang / Semua)
        $totalAnggaranQuery = MasterKegiatan::query();
        if ($bidangId !== null) {
            $totalAnggaranQuery->where('bidang_id', $bidangId);
        }
        $totalAnggaran = (float) $totalAnggaranQuery->sum('pagu_anggaran');

        // 2. Total Kegiatan & Sub Kegiatan
        $totalKegiatanQuery = MasterKegiatan::query();
        $totalSubKegiatanQuery = MasterSubKegiatan::query();
        if ($bidangId !== null) {
            $totalKegiatanQuery->where('bidang_id', $bidangId);
            $totalSubKegiatanQuery->whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $bidangId));
        }
        $totalKegiatan = $totalKegiatanQuery->count();
        $totalSubKegiatan = $totalSubKegiatanQuery->count();

        // 3. Total Barang Habis (Stok <= 0 pada Bidang / Semua)
        $totalBarangHabisQuery = StokBarang::query()->where('stok_tersedia', '<=', 0);
        if ($bidangId !== null) {
            $totalBarangHabisQuery->where('bidang_id', $bidangId);
        }
        $totalBarangHabis = $totalBarangHabisQuery->count();

        // 4. Total Uang Sudah Di-SPJ-kan (Realisasi Belanja)
        $validStatuses = [
            'dicairkan',
            'disetujui',
            'menunggu_verifikasi_bendahara',
            'menunggu_persetujuan_pa',
            'menunggu_verifikasi_ppk',
            'menunggu_verifikasi_pptk',
        ];

        $spjQuery = PengajuanSpj::query();
        if ($bidangId !== null) {
            $spjQuery->where('bidang_id', $bidangId);
        }

        $totalSudahSpj = (float) (clone $spjQuery)->whereIn('status', $validStatuses)->sum('total_nilai_realisasi');
        $totalSpjCair = (clone $spjQuery)->where('status', 'dicairkan')->count();
        $totalSemuaSpj = (clone $spjQuery)->count();

        // Sisa Anggaran (Pagu - Realisasi SPJ)
        $sisaAnggaran = max(0, $totalAnggaran - $totalSudahSpj);

        $filterParams = $bidangId !== null ? [
            'bidang' => $bidangId,
        ] : [];

        return [
            // Stat 1: Total Anggaran
            Stat::make('Total Anggaran', 'Rp ' . number_format($totalAnggaran, 0, ',', '.'))
                ->description('Sisa pagu: Rp ' . number_format($sisaAnggaran, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->url(MasterKegiatanResource::getUrl('index', $filterParams)),

            // Stat 2: Total Kegiatan
            Stat::make('Total Kegiatan', $totalKegiatan . ' Kegiatan')
                ->description($totalSubKegiatan . ' Sub-Kegiatan aktif')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('info')
                ->url(MasterKegiatanResource::getUrl('index', $filterParams)),

            // Stat 3: Total Barang Habis
            Stat::make('Total Barang Habis', $totalBarangHabis . ' Item Habis')
                ->description($totalBarangHabis > 0 ? 'Perlu pengadaan / restock segera' : 'Semua stok persediaan aman')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalBarangHabis > 0 ? 'danger' : 'gray')
                ->url(MasterBarangResource::getUrl('index', $filterParams)),

            // Stat 4: Total Uang Sudah Di-SPJ-kan
            Stat::make('Total Sudah Di-SPJ-kan', 'Rp ' . number_format($totalSudahSpj, 0, ',', '.'))
                ->description($totalSpjCair . ' cair dari ' . $totalSemuaSpj . ' berkas SPJ')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('warning')
                ->url(PengajuanSpjResource::getUrl('index', $filterParams)),
        ];
    }
}
