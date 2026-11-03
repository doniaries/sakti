<?php

declare(strict_types=1);

namespace App\Filament\Resources\PengajuanSpjs\Widgets;

use App\Models\Bidang;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class KeuanganBidangOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected ?string $heading = 'Ringkasan Keuangan, Kegiatan & Stok per Bidang';

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $bidangs = Bidang::query()
            ->select(['id', 'nama', 'slug', 'kepala_id'])
            ->with(['kepala:id,name,nip'])
            ->withCount([
                'masterKegiatans',
                'stokBarangs as stok_ada_count' => fn ($q) => $q->where('stok_tersedia', '>', 0),
                'stokBarangs as stok_habis_count' => fn ($q) => $q->where('stok_tersedia', '<=', 0),
            ])
            ->withSum('masterKegiatans as total_uang_kegiatan', 'pagu_anggaran')
            ->withSum('stokBarangs as total_unit_stok', 'stok_tersedia')
            ->get();

        $colors = ['primary', 'info', 'success', 'warning'];
        $stats = [];

        foreach ($bidangs as $idx => $bidang) {
            $namaPptk = e($bidang->kepala?->name ?? 'Belum Ditentukan');
            $jmlKegiatan = (int) $bidang->master_kegiatans_count;
            $uangKegiatan = (float) ($bidang->total_uang_kegiatan ?? 0);
            $stokAda = (int) $bidang->stok_ada_count;
            $stokHabis = (int) $bidang->stok_habis_count;
            $totalUnit = (int) ($bidang->total_unit_stok ?? 0);
            $color = $colors[$idx % count($colors)];

            $htmlDescription = new HtmlString(sprintf(
                '<div style="display:flex; flex-direction:column; gap:3px; font-size:0.78rem; line-height:1.35; margin-top:2px;">
                    <div><strong>PPTK:</strong> %s</div>
                    <div><strong>Jumlah Kegiatan:</strong> %d Kegiatan</div>
                    <div>
                        <span style="color:#16a34a; font-weight:600;">Stok Ada: %d (%d Unit)</span>
                        &nbsp;|&nbsp;
                        <span style="color:#dc2626; font-weight:600;">Habis: %d</span>
                    </div>
                </div>',
                $namaPptk,
                $jmlKegiatan,
                $stokAda,
                $totalUnit,
                $stokHabis
            ));

            $stats[] = Stat::make($bidang->nama, 'Rp ' . number_format($uangKegiatan, 0, ',', '.'))
                ->description($htmlDescription)
                ->color($color);
        }

        return $stats;
    }
}

