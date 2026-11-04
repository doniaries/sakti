<?php

namespace App\Filament\Resources\MasterTokos\Pages;

use App\Filament\Resources\MasterBarangs\MasterBarangResource;
use App\Filament\Resources\MasterTokos\MasterTokoResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Filament\Resources\StokBarangs\StokBarangResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterTokos extends ListRecords
{
    protected static string $resource = MasterTokoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Toko / Rekanan')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),

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

            Action::make('kelola_spj')
                ->label('SPJ Belanja')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('gray')
                ->url(fn (): string => PengajuanSpjResource::getUrl('index')),
        ];
    }
}
