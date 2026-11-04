<?php

namespace App\Filament\Resources\MasterKegiatans\Pages;

use App\Filament\Resources\KodeRekenings\KodeRekeningResource;
use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\MasterPrograms\MasterProgramResource;
use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterKegiatans extends ListRecords
{
    protected static string $resource = MasterKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kegiatan')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),

            Action::make('kelola_program')
                ->label('Kelola Program')
                ->icon('heroicon-o-folder')
                ->color('gray')
                ->url(fn (): string => MasterProgramResource::getUrl('index')),

            Action::make('kelola_sub_kegiatan')
                ->label('Kelola Sub-Kegiatan')
                ->icon('heroicon-o-rectangle-stack')
                ->color('gray')
                ->url(fn (): string => MasterSubKegiatanResource::getUrl('index')),

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
