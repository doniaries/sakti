<?php

namespace App\Filament\Resources\AbsensiHarians\Pages;

use App\Filament\Resources\AbsensiHarians\AbsensiHarianResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Leek\FilamentHeaderFilters\Concerns\HasHeaderFilters;

class ListAbsensiHarians extends ListRecords
{
    use HasHeaderFilters;

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
            'jumlah_pegawai' => Tab::make('Jumlah Pegawai')
                ->modifyQueryUsing(fn($query) => $query->whereDate('tanggal', \Carbon\Carbon::today()))
                ->badge(AbsensiHarianResource::getEloquentQuery()->whereDate('tanggal', \Carbon\Carbon::today())->count())
                ->badgeColor('info'),

            'hadir' => Tab::make('Hadir')
                ->modifyQueryUsing(fn($query) => $query->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'hadir'))
                ->badge(AbsensiHarianResource::getEloquentQuery()->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'hadir')->count())
                ->badgeColor('success'),

            'sakit' => Tab::make('Sakit')
                ->modifyQueryUsing(fn($query) => $query->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'sakit'))
                ->badge(AbsensiHarianResource::getEloquentQuery()->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'sakit')->count())
                ->badgeColor('warning'),

            'izin' => Tab::make('Izin')
                ->modifyQueryUsing(fn($query) => $query->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'izin'))
                ->badge(AbsensiHarianResource::getEloquentQuery()->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'izin')->count())
                ->badgeColor('warning'),

            'tidak_hadir' => Tab::make('Tanpa Keterangan')
                ->modifyQueryUsing(fn($query) => $query->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'tidak_hadir'))
                ->badge(AbsensiHarianResource::getEloquentQuery()->whereDate('tanggal', \Carbon\Carbon::today())->where('status', 'tidak_hadir')->count())
                ->badgeColor('danger'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
