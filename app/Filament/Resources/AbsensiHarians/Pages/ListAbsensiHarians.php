<?php

namespace App\Filament\Resources\AbsensiHarians\Pages;

use App\Filament\Resources\AbsensiHarians\AbsensiHarianResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Leek\FilamentHeaderFilters\Concerns\HasHeaderFilters;

class ListAbsensiHarians extends ListRecords
{
    // HasHeaderFilters tidak diperlukan karena filter sudah menggunakan AboveContent layout

    protected static string $resource = AbsensiHarianResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\AbsensiHarians\Widgets\AbsensiHarianStats::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Data'),

            'hadir' => Tab::make('Hadir')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'hadir')),

            'sakit' => Tab::make('Sakit')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'sakit')),

            'izin' => Tab::make('Izin')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'izin')),

            'tidak_hadir' => Tab::make('Tanpa Keterangan')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'tidak_hadir')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
