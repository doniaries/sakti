<?php

declare(strict_types=1);

namespace App\Filament\Resources\BukuInventaris\Pages;

use App\Filament\Resources\BukuInventaris\BukuInventarisResource;
use App\Filament\Resources\BukuInventaris\Widgets\BukuInventarisOverviewWidget;
use App\Models\BukuInventaris;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBukuInventaris extends ListRecords
{
    protected static string $resource = BukuInventarisResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            BukuInventarisOverviewWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Aset')
                ->badge(BukuInventaris::count()),

            'baik' => Tab::make('Kondisi Baik')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('keadaan_barang', 'Baik'))
                ->badge(BukuInventaris::where('keadaan_barang', 'Baik')->count())
                ->badgeColor('success'),

            'kurang_baik' => Tab::make('Kurang Baik')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('keadaan_barang', 'Kurang Baik'))
                ->badge(BukuInventaris::where('keadaan_barang', 'Kurang Baik')->count())
                ->badgeColor('warning'),

            'rusak_berat' => Tab::make('Rusak Berat')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('keadaan_barang', 'Rusak Berat'))
                ->badge(BukuInventaris::where('keadaan_barang', 'Rusak Berat')->count())
                ->badgeColor('danger'),

            'gedung' => Tab::make('Gedung & Bangunan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kode_barang', 'like', '01%'))
                ->badge(BukuInventaris::where('kode_barang', 'like', '01%')->count())
                ->badgeColor('info'),

            'peralatan' => Tab::make('Peralatan & Mesin')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kode_barang', 'like', '02%'))
                ->badge(BukuInventaris::where('kode_barang', 'like', '02%')->count())
                ->badgeColor('primary'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pratinjau_html')
                ->label('Pratinjau Laporan')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn (): string => route('admin.buku-inventaris.cetak', ['preview' => 1]))
                ->openUrlInNewTab(),

            Action::make('cetak_pdf')
                ->label('Cetak Buku Inventaris (PDF)')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->url(fn (): string => route('admin.buku-inventaris.cetak'))
                ->openUrlInNewTab(),

            CreateAction::make()
                ->label('Tambah Aset')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }
}
