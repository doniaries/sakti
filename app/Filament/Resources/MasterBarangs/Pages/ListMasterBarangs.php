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
                ->label('Tambah Kode Barang')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }
}
