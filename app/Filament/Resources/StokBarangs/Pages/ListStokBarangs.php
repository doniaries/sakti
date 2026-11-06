<?php

declare(strict_types=1);

namespace App\Filament\Resources\StokBarangs\Pages;

use App\Filament\Resources\MasterBarangs\MasterBarangResource;
use App\Filament\Resources\MasterTokos\MasterTokoResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Filament\Resources\StokBarangs\StokBarangResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStokBarangs extends ListRecords
{
    protected static string $resource = StokBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Stok Persediaan')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }
}
