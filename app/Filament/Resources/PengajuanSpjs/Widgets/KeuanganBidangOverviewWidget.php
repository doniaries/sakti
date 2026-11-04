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

        if ($user !== null && filled($user->bidang_id)) {
            $this->selectedBidangId = (int) $user->bidang_id;
        } else {
            $this->selectedBidangId = (int) (Bidang::first()?->id ?? 1);
        }
    }

    public static function canView(): bool
    {
        return Auth::check();
    }

    protected function getColumns(): int
    {
        return 4;
    }

    public function selectBidang(int $id): void
    {
        if ($this->canChooseBidang()) {
            $this->selectedBidangId = $id;
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

        if ($this->selectedBidangId !== null) {
            $bidang = Bidang::with('kepala:id,name,nip')->find($this->selectedBidangId);
            if ($bidang !== null) {
                return $bidang;
            }
        }

        return Bidang::with('kepala:id,name,nip')->first();
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
        $pptk = $bidang?->kepala?->name ? " (PPTK: {$bidang->kepala->name})" : '';

        return "Pedoman alokasi anggaran, kegiatan, stok kritis barang, dan realisasi SPJ{$pptk} sebelum belanja";
    }

    protected function getStats(): array
    {
        $bidang = $this->getActiveBidang();
        if ($bidang === null) {
            return [];
        }

        $bidangId = $bidang->id;

        // 1. Total Anggaran (Pagu Kegiatan Bidang)
        $totalAnggaran = (float) MasterKegiatan::where('bidang_id', $bidangId)->sum('pagu_anggaran');

        // 2. Total Kegiatan & Sub Kegiatan Bidang
        $totalKegiatan = MasterKegiatan::where('bidang_id', $bidangId)->count();
        $totalSubKegiatan = MasterSubKegiatan::whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $bidangId))->count();

        // 3. Total Barang Habis (Stok <= 0 pada Bidang)
        $totalBarangHabis = StokBarang::where('bidang_id', $bidangId)->where('stok_tersedia', '<=', 0)->count();
        $totalJenisBarang = StokBarang::where('bidang_id', $bidangId)->count();

        // 4. Total Uang Sudah Di-SPJ-kan (Realisasi Belanja)
        $totalSudahSpj = (float) PengajuanSpj::where('bidang_id', $bidangId)
            ->whereIn('status', [
                'dicairkan',
                'disetujui',
                'menunggu_verifikasi_bendahara',
                'menunggu_persetujuan_pa',
                'menunggu_verifikasi_ppk',
                'menunggu_verifikasi_pptk',
            ])
            ->sum('total_nilai_realisasi');

        $totalSpjCair = PengajuanSpj::where('bidang_id', $bidangId)
            ->where('status', 'dicairkan')
            ->count();

        $totalSemuaSpj = PengajuanSpj::where('bidang_id', $bidangId)->count();

        // Sisa Anggaran (Pagu - Realisasi SPJ)
        $sisaAnggaran = max(0, $totalAnggaran - $totalSudahSpj);

        return [
            // Stat 1: Total Anggaran
            Stat::make('Total Anggaran', 'Rp ' . number_format($totalAnggaran, 0, ',', '.'))
                ->description('Sisa pagu: Rp ' . number_format($sisaAnggaran, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->url(MasterKegiatanResource::getUrl('index')),

            // Stat 2: Total Kegiatan
            Stat::make('Total Kegiatan', $totalKegiatan . ' Kegiatan')
                ->description($totalSubKegiatan . ' Sub-Kegiatan aktif')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('info')
                ->url(MasterKegiatanResource::getUrl('index')),

            // Stat 3: Total Barang Habis
            Stat::make('Total Barang Habis', $totalBarangHabis . ' Item Habis')
                ->description($totalBarangHabis > 0 ? 'Perlu pengadaan / restock segera' : 'Semua stok persediaan aman')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalBarangHabis > 0 ? 'danger' : 'gray')
                ->url(MasterBarangResource::getUrl('index')),

            // Stat 4: Total Uang Sudah Di-SPJ-kan
            Stat::make('Total Sudah Di-SPJ-kan', 'Rp ' . number_format($totalSudahSpj, 0, ',', '.'))
                ->description($totalSpjCair . ' cair dari ' . $totalSemuaSpj . ' berkas SPJ')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('warning')
                ->url(PengajuanSpjResource::getUrl('index')),
        ];
    }
}
