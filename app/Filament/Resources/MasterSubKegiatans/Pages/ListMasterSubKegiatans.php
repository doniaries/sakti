<?php

namespace App\Filament\Resources\MasterSubKegiatans\Pages;

use App\Filament\Resources\KodeRekenings\KodeRekeningResource;
use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\MasterPrograms\MasterProgramResource;
use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Filament\Resources\PengajuanSpjs\Widgets\KeuanganBidangOverviewWidget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterSubKegiatans extends ListRecords
{
    protected static string $resource = MasterSubKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Sub-Kegiatan')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),

            Action::make('kelola_kegiatan')
                ->label('Kelola Kegiatan')
                ->icon('heroicon-o-queue-list')
                ->color('gray')
                ->url(fn (): string => MasterKegiatanResource::getUrl('index')),

            Action::make('kelola_program')
                ->label('Kelola Program')
                ->icon('heroicon-o-folder')
                ->color('gray')
                ->url(fn (): string => MasterProgramResource::getUrl('index')),

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

    protected function getHeaderWidgets(): array
    {
        return [
            KeuanganBidangOverviewWidget::class,
        ];
    }
}
