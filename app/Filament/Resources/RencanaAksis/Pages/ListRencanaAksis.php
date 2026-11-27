<?php

namespace App\Filament\Resources\RencanaAksis\Pages;

use App\Filament\Resources\RencanaAksis\RencanaAksiResource;
use App\Models\RencanaAksi;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ListRencanaAksis extends ListRecords
{
    protected static string $resource = RencanaAksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'semua' => Tab::make('Semua')
                ->badge(RencanaAksi::count()),
        ];

        $subKegiatans = RencanaAksi::query()
            ->whereNotNull('sasaran_sub_kegiatan')
            ->selectRaw('sasaran_sub_kegiatan, COUNT(*) as total')
            ->groupBy('sasaran_sub_kegiatan')
            ->orderBy('sasaran_sub_kegiatan')
            ->get();

        foreach ($subKegiatans as $row) {
            $sub = (string) $row->sasaran_sub_kegiatan;
            $tabs['sub_' . md5($sub)] = Tab::make(Str::limit($sub, 32))
                ->badge((int) $row->total)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('sasaran_sub_kegiatan', $sub));
        }

        return $tabs;
    }
}
