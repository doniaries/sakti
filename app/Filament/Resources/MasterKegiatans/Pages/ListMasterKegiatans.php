<?php

namespace App\Filament\Resources\MasterKegiatans\Pages;

use App\Filament\Resources\KodeRekenings\KodeRekeningResource;
use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\MasterPrograms\MasterProgramResource;
use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Models\Bidang;
use App\Models\MasterKegiatan;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMasterKegiatans extends ListRecords
{
    protected static string $resource = MasterKegiatanResource::class;

    /**
     * Filter Tab Bidang di Header Tabel untuk kemudahan navigasi & menghilangkan filter dropdown yang berantakan.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $isGlobal = $user && $user->canViewAllBidangKeuangan();

        $selectedTahun = \App\Models\PengaturanAplikasi::getTahunAnggaranAktif();

        // Jika PPTK / non-pemeriksa, tampilkan HANYA tab nama bidangnya saja
        if (! $isGlobal && $user?->bidang_id) {
            $bidang = Bidang::find($user->bidang_id);
            if ($bidang) {
                $icon = match ($bidang->slug) {
                    'sekretariat' => 'heroicon-m-building-office',
                    'pemasaran-pariwisata', 'bidang-pemasaran-pariwisata' => 'heroicon-m-megaphone',
                    'pengembangan-destinasi-pariwisata' => 'heroicon-m-map-pin',
                    'pemuda-dan-olahraga' => 'heroicon-m-trophy',
                    default => 'heroicon-m-briefcase',
                };

                $count = MasterKegiatan::where('tahun_anggaran', (int) $selectedTahun)
                    ->where('bidang_id', $bidang->id)
                    ->count();

                return [
                    $bidang->slug ?: 'bidang_' . $bidang->id => Tab::make($bidang->nama)
                        ->icon($icon)
                        ->badge($count)
                        ->modifyQueryUsing(fn (Builder $query) => $query->where('bidang_id', $bidang->id)),
                ];
            }
        }

        $baseQuery = MasterKegiatan::query()->where('tahun_anggaran', (int) $selectedTahun);

        $tabs = [
            'semua' => Tab::make('Semua')
                ->icon('heroicon-m-squares-2x2')
                ->badge($baseQuery->count()),
        ];

        $bidangQuery = Bidang::query()->orderBy('nama');
        $bidangs = $bidangQuery->get();

        $countsPerBidang = MasterKegiatan::query()
            ->where('tahun_anggaran', (int) $selectedTahun)
            ->groupBy('bidang_id')
            ->selectRaw('bidang_id, count(*) as aggregate')
            ->pluck('aggregate', 'bidang_id');

        foreach ($bidangs as $bidang) {
            $icon = match ($bidang->slug) {
                'sekretariat' => 'heroicon-m-building-office',
                'pemasaran-pariwisata', 'bidang-pemasaran-pariwisata' => 'heroicon-m-megaphone',
                'pengembangan-destinasi-pariwisata' => 'heroicon-m-map-pin',
                'pemuda-dan-olahraga' => 'heroicon-m-trophy',
                default => 'heroicon-m-briefcase',
            };

            $count = (int) ($countsPerBidang[$bidang->id] ?? 0);

            $tabs[$bidang->slug ?: 'bidang_' . $bidang->id] = Tab::make($bidang->nama)
                ->icon($icon)
                ->badge($count)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('bidang_id', $bidang->id));
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kegiatan')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }
}
