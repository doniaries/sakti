<?php

namespace App\Filament\Resources\MasterJenisBelanjas\Pages;

use App\Filament\Resources\MasterJenisBelanjas\MasterJenisBelanjaResource;
use App\Models\MasterJenisBelanja;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMasterJenisBelanjas extends ListRecords
{
    protected static string $resource = MasterJenisBelanjaResource::class;

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Pekerjaan')
                ->icon('heroicon-o-list-bullet')
                ->badge(MasterJenisBelanja::count()),

            'non_katalog' => Tab::make('Non-Katalog (Pengadaan Langsung)')
                ->icon('heroicon-o-building-storefront')
                ->badge(MasterJenisBelanja::where('metode_pengadaan', 'non_katalog')->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('metode_pengadaan', 'non_katalog')),

            'e_katalog' => Tab::make('E-Katalog LKPP (E-Purchasing)')
                ->icon('heroicon-o-shopping-bag')
                ->badge(MasterJenisBelanja::where('metode_pengadaan', 'e_katalog')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('metode_pengadaan', 'e_katalog')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Jenis Pekerjaan Baru')
                ->icon('heroicon-o-plus'),
        ];
    }
}

