<?php

namespace App\Filament\Resources\Jabatans\Pages;

use App\Filament\Resources\Bidangs\BidangResource;
use App\Filament\Resources\Jabatans\JabatanResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJabatans extends ListRecords
{
    protected static string $resource = JabatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Jabatan')
                ->icon('heroicon-o-plus')
                ->color('primary'),

            Action::make('kelola_bidang')
                ->label('Kelola Bidang')
                ->icon('heroicon-o-building-office-2')
                ->color('gray')
                ->url(fn (): string => BidangResource::getUrl('index')),

            Action::make('data_pegawai')
                ->label('Data Pegawai')
                ->icon('heroicon-o-users')
                ->color('gray')
                ->url(fn (): string => UserResource::getUrl('index')),
        ];
    }
}
