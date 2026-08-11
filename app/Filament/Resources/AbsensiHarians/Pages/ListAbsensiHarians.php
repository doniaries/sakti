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
                ->modifyQueryUsing(fn($query) => $query->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'hadir'))
                ->badge(\App\Models\AbsensiHarian::whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'hadir')->count()),

            'sakit' => Tab::make('Sakit')
                ->modifyQueryUsing(fn($query) => $query->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'sakit'))
                ->badge(\App\Models\AbsensiHarian::whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'sakit')->count()),

            'izin' => Tab::make('Izin')
                ->modifyQueryUsing(fn($query) => $query->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'izin'))
                ->badge(\App\Models\AbsensiHarian::whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'izin')->count()),

            'tidak_hadir' => Tab::make('Tanpa Keterangan')
                ->modifyQueryUsing(fn($query) => $query->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'tidak_hadir'))
                ->badge(\App\Models\AbsensiHarian::whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'tidak_hadir')->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
