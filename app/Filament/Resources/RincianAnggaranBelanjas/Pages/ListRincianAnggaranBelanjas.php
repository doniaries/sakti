<?php

namespace App\Filament\Resources\RincianAnggaranBelanjas\Pages;

use App\Filament\Resources\RincianAnggaranBelanjas\RincianAnggaranBelanjaResource;
use App\Models\Bidang;
use App\Models\RincianAnggaranBelanja;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListRincianAnggaranBelanjas extends ListRecords
{
    protected static string $resource = RincianAnggaranBelanjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Input Rincian Belanja (RKA)')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\RincianAnggaranBelanjas\Widgets\RkaOverviewWidget::class,
        ];
    }

    /**
     * Tab Filter Bidang:
     * - PPTK (Kepala Bidang) & Staf Bidang: Hanya melihat tab nama bidangnya saja.
     * - Pemeriksa (PPK, PA, Bendahara, Super Admin): Melihat tab 'Semua' dan seluruh bidang.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $canViewAll = $user?->canViewAllBidangKeuangan() ?? false;

        $selectedTahun = request()->query('tahun')
            ?? data_get(request()->query('tableFilters'), 'tahun_anggaran.value')
            ?? (int) date('Y');

        // Jika PPTK / non-pemeriksa, kunci hanya pada bidang miliknya
        if (! $canViewAll && $user?->bidang_id) {
            $bidang = Bidang::find($user->bidang_id);
            if ($bidang) {
                $count = RincianAnggaranBelanja::where('tahun_anggaran', (int) $selectedTahun)
                    ->where('bidang_id', $bidang->id)
                    ->count();

                return [
                    $bidang->slug ?: 'bidang_' . $bidang->id => Tab::make($bidang->nama)
                        ->icon('heroicon-m-briefcase')
                        ->badge($count)
                        ->modifyQueryUsing(fn (Builder $query) => $query->where('bidang_id', $bidang->id)),
                ];
            }
        }

        // Untuk Pemeriksa (PPK, PA, Bendahara, Super Admin)
        $tabs = [
            'semua' => Tab::make('Semua Bidang')
                ->icon('heroicon-m-squares-2x2')
                ->badge(RincianAnggaranBelanja::where('tahun_anggaran', (int) $selectedTahun)->count()),
        ];

        $bidangs = Bidang::query()->orderBy('nama')->get();
        foreach ($bidangs as $bidang) {
            $icon = match ($bidang->slug) {
                'sekretariat' => 'heroicon-m-building-office',
                'pemasaran-pariwisata', 'bidang-pemasaran-pariwisata' => 'heroicon-m-megaphone',
                'pengembangan-destinasi-pariwisata' => 'heroicon-m-map-pin',
                'pemuda-dan-olahraga' => 'heroicon-m-trophy',
                default => 'heroicon-m-briefcase',
            };

            $count = RincianAnggaranBelanja::where('tahun_anggaran', (int) $selectedTahun)
                ->where('bidang_id', $bidang->id)
                ->count();

            $tabs[$bidang->slug ?: 'bidang_' . $bidang->id] = Tab::make($bidang->nama)
                ->icon($icon)
                ->badge($count)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('bidang_id', $bidang->id));
        }

        return $tabs;
    }
}
