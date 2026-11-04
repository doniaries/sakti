<?php

namespace App\Filament\Resources\KodeRekenings\Pages;

use App\Filament\Resources\KodeRekenings\KodeRekeningResource;
use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\MasterPrograms\MasterProgramResource;
use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Filament\Resources\PengajuanSpjs\Widgets\KeuanganBidangOverviewWidget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKodeRekenings extends ListRecords
{
    protected static string $resource = KodeRekeningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kode Rekening')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),

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

            Action::make('kelola_program')
                ->label('Program')
                ->icon('heroicon-o-folder')
                ->color('gray')
                ->url(fn (): string => MasterProgramResource::getUrl('index')),

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
