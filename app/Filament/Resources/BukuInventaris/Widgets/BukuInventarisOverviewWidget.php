<?php

declare(strict_types=1);

namespace App\Filament\Resources\BukuInventaris\Widgets;

use App\Filament\Resources\BukuInventaris\BukuInventarisResource;
use App\Models\BukuInventaris;
use App\Support\FormatHelper;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BukuInventarisOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $totalItem = BukuInventaris::count();
        $totalHarga = (float) BukuInventaris::sum('harga');

        $baikCount = BukuInventaris::where('keadaan_barang', 'Baik')->count();
        $baikHarga = (float) BukuInventaris::where('keadaan_barang', 'Baik')->sum('harga');

        $kbCount = BukuInventaris::where('keadaan_barang', 'Kurang Baik')->count();
        $kbHarga = (float) BukuInventaris::where('keadaan_barang', 'Kurang Baik')->sum('harga');

        $rbCount = BukuInventaris::where('keadaan_barang', 'Rusak Berat')->count();
        $rbHarga = (float) BukuInventaris::where('keadaan_barang', 'Rusak Berat')->sum('harga');

        return [
            Stat::make('Total Nilai Aset', FormatHelper::rupiah($totalHarga))
                ->description("{$totalItem} total item inventaris")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary')
                ->url(BukuInventarisResource::getUrl('index', ['activeTab' => 'semua'])),

            Stat::make('Kondisi Baik (B)', "{$baikCount} Item")
                ->description(FormatHelper::rupiah($baikHarga) . ' • Siap pakai')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(BukuInventarisResource::getUrl('index', ['activeTab' => 'baik'])),

            Stat::make('Kurang Baik (KB)', "{$kbCount} Item")
                ->description(FormatHelper::rupiah($kbHarga) . ' • Perlu perbaikan')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning')
                ->url(BukuInventarisResource::getUrl('index', ['activeTab' => 'kurang_baik'])),

            Stat::make('Rusak Berat (RB)', "{$rbCount} Item")
                ->description(FormatHelper::rupiah($rbHarga) . ' • Usul hapus')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(BukuInventarisResource::getUrl('index', ['activeTab' => 'rusak_berat'])),
        ];
    }
}
