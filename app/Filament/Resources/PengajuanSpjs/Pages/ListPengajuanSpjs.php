<?php

namespace App\Filament\Resources\PengajuanSpjs\Pages;

use App\Filament\Resources\KodeRekenings\KodeRekeningResource;
use App\Filament\Resources\MasterBarangs\MasterBarangResource;
use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Filament\Resources\PengajuanSpjs\Widgets\KeuanganBidangOverviewWidget;
use App\Filament\Resources\StokBarangs\StokBarangResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanSpjs extends ListRecords
{
    protected static string $resource = PengajuanSpjResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Pengajuan SPJ')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),

            Action::make('rka_belanja')
                ->label('Rincian Belanja (RKA)')
                ->icon('heroicon-o-document-chart-bar')
                ->color('gray')
                ->url(fn (): string => \App\Filament\Resources\RincianAnggaranBelanjas\RincianAnggaranBelanjaResource::getUrl('index')),

            Action::make('kelola_sub_kegiatan')
                ->label('Sub-Kegiatan')
                ->icon('heroicon-o-rectangle-stack')
                ->color('gray')
                ->url(fn (): string => MasterSubKegiatanResource::getUrl('index')),

            Action::make('kelola_kegiatan')
                ->label('Kegiatan')
                ->icon('heroicon-o-queue-list')
                ->color('gray')
                ->url(fn (): string => MasterKegiatanResource::getUrl('index')),

            Action::make('kelola_rekening')
                ->label('Kode Rekening')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('gray')
                ->url(fn (): string => KodeRekeningResource::getUrl('index')),

            Action::make('kelola_barang')
                ->label('Master Barang')
                ->icon('heroicon-o-cube')
                ->color('gray')
                ->url(fn (): string => MasterBarangResource::getUrl('index')),

            Action::make('kelola_stok')
                ->label('Stok Persediaan')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->url(fn (): string => StokBarangResource::getUrl('index')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            KeuanganBidangOverviewWidget::class,
        ];
    }
}
