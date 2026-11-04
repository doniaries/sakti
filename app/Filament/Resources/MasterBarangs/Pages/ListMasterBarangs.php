<?php

namespace App\Filament\Resources\MasterBarangs\Pages;

use App\Filament\Resources\KodeRekenings\KodeRekeningResource;
use App\Filament\Resources\MasterBarangs\MasterBarangResource;
use App\Filament\Resources\MasterTokos\MasterTokoResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Filament\Resources\StokBarangs\StokBarangResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterBarangs extends ListRecords
{
    protected static string $resource = MasterBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Master Barang')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),

            Action::make('kelola_stok')
                ->label('Stok & Mutasi')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->url(fn (): string => StokBarangResource::getUrl('index')),

            Action::make('kelola_toko')
                ->label('Toko Rekanan')
                ->icon('heroicon-o-building-storefront')
                ->color('gray')
                ->url(fn (): string => MasterTokoResource::getUrl('index')),

            Action::make('kelola_rekening')
                ->label('Kode Rekening')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('gray')
                ->url(fn (): string => KodeRekeningResource::getUrl('index')),

            Action::make('kelola_spj')
                ->label('SPJ Belanja')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('gray')
                ->url(fn (): string => PengajuanSpjResource::getUrl('index')),
        ];
    }
}
