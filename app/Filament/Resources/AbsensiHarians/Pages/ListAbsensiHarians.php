<?php

namespace App\Filament\Resources\AbsensiHarians\Pages;

use App\Filament\Resources\AbsensiHarians\AbsensiHarianResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListAbsensiHarians extends ListRecords
{
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
            'hadir' => Tab::make('Hadir')
                ->modifyQueryUsing(fn($query) => $query->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'hadir')),
            'sakit' => Tab::make('Sakit')
                ->modifyQueryUsing(fn($query) => $query->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'sakit')),
            'izin' => Tab::make('Izin')
                ->modifyQueryUsing(fn($query) => $query->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'izin')),
            'tidak_hadir' => Tab::make('Tanpa Keterangan')
                ->modifyQueryUsing(fn($query) => $query->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'tidak_hadir')),
            'semua' => Tab::make('Semua Waktu'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
