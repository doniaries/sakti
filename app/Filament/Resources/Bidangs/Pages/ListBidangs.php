<?php

namespace App\Filament\Resources\Bidangs\Pages;

use App\Filament\Resources\Bidangs\BidangResource;
use App\Filament\Resources\Jabatans\JabatanResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBidangs extends ListRecords
{
    protected static string $resource = BidangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Bidang')
                ->icon('heroicon-o-plus')
                ->color('primary'),

            Action::make('kelola_jabatan')
                ->label('Kelola Jabatan')
                ->icon('heroicon-o-tag')
                ->color('gray')
                ->url(fn (): string => JabatanResource::getUrl('index')),

            Action::make('data_pegawai')
                ->label('Data Pegawai')
                ->icon('heroicon-o-users')
                ->color('gray')
                ->url(fn (): string => UserResource::getUrl('index')),
        ];
    }
}
