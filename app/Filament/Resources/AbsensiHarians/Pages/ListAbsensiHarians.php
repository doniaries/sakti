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
        $currentUser = \Illuminate\Support\Facades\Auth::user();
        $baseQuery = AbsensiHarianResource::getEloquentQuery();
        
        if ($currentUser && ! $currentUser->hasAnyRole(['super_admin', 'admin', 'pimpinan'])) {
            $baseQuery->where('user_id', $currentUser->id);
        } else {
            $userId = $this->tableFilters['pegawai']['user_id'] ?? $currentUser?->id;
            if ($userId) {
                $baseQuery->where('user_id', $userId);
            }
        }
        
        if (!empty($this->tableFilters['bulan']['bulan'])) {
            $baseQuery->whereMonth('tanggal', $this->tableFilters['bulan']['bulan']);
        }
        
        if (!empty($this->tableFilters['tahun']['tahun'])) {
            $baseQuery->whereYear('tanggal', $this->tableFilters['tahun']['tahun']);
        }

        return [
            'semua' => Tab::make('Semua Data')
                ->badge((clone $baseQuery)->count()),

            'hadir' => Tab::make('Hadir')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'hadir'))
                ->badge((clone $baseQuery)->where('status', 'hadir')->count())
                ->badgeColor('success'),

            'sakit' => Tab::make('Sakit')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'sakit'))
                ->badge((clone $baseQuery)->where('status', 'sakit')->count())
                ->badgeColor('warning'),

            'izin' => Tab::make('Izin')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'izin'))
                ->badge((clone $baseQuery)->where('status', 'izin')->count())
                ->badgeColor('warning'),

            'tidak_hadir' => Tab::make('Tanpa Keterangan')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'tidak_hadir'))
                ->badge((clone $baseQuery)->where('status', 'tidak_hadir')->count())
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
