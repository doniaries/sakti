<?php

namespace App\Filament\Resources\MasterSyaratBelanjas\Pages;

use App\Filament\Resources\MasterSyaratBelanjas\MasterSyaratBelanjaResource;
use App\Models\MasterSyaratBelanja;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMasterSyaratBelanjas extends ListRecords
{
    protected static string $resource = MasterSyaratBelanjaResource::class;

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Syarat')
                ->icon('heroicon-o-list-bullet')
                ->badge(MasterSyaratBelanja::count()),

            'non_katalog' => Tab::make('Non-Katalog (Pengadaan Langsung)')
                ->icon('heroicon-o-building-storefront')
                ->badge(MasterSyaratBelanja::where('metode_pengadaan', 'non_katalog')->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('metode_pengadaan', 'non_katalog')),

            'e_katalog' => Tab::make('E-Katalog LKPP (E-Purchasing)')
                ->icon('heroicon-o-shopping-bag')
                ->badge(MasterSyaratBelanja::where('metode_pengadaan', 'e_katalog')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('metode_pengadaan', 'e_katalog')),

            'umum' => Tab::make('Dokumen Umum / Tambahan')
                ->icon('heroicon-o-document-duplicate')
                ->badge(MasterSyaratBelanja::where(fn ($q) => $q->where('jenis_belanja', 'semua')->orWhere('metode_pengadaan', 'semua'))->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where(fn ($q) => $q->where('jenis_belanja', 'semua')->orWhere('metode_pengadaan', 'semua'))),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Syarat Dokumen')
                ->icon('heroicon-o-plus')
                ->slideOver(),
        ];
    }

    public function swapUrutan(int $id, string $direction): void
    {
        $item = MasterSyaratBelanja::find($id);
        if (! $item) {
            return;
        }

        $query = MasterSyaratBelanja::query()
            ->where('jenis_belanja', $item->jenis_belanja)
            ->where('metode_pengadaan', $item->metode_pengadaan);

        if ($direction === 'up') {
            $neighbor = (clone $query)->where('urutan', '<', $item->urutan)->orderByDesc('urutan')->first();
        } else {
            $neighbor = (clone $query)->where('urutan', '>', $item->urutan)->orderBy('urutan')->first();
        }

        if ($neighbor) {
            $oldUrutan = $item->urutan;
            $item->urutan = $neighbor->urutan;
            $neighbor->urutan = $oldUrutan;
            $item->save();
            $neighbor->save();
        }
    }
}
