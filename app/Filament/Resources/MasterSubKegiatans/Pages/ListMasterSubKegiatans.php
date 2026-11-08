<?php

namespace App\Filament\Resources\MasterSubKegiatans\Pages;

use App\Filament\Resources\KodeRekenings\KodeRekeningResource;
use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\MasterPrograms\MasterProgramResource;
use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Models\Bidang;
use App\Models\MasterSubKegiatan;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMasterSubKegiatans extends ListRecords
{
    protected static string $resource = MasterSubKegiatanResource::class;

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

        $selectedTahun = request()->query('tahun')
            ?? data_get(request()->query('tableFilters'), 'tahun_anggaran.value')
            ?? (int) date('Y');

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

                $count = MasterSubKegiatan::where('tahun_anggaran', (int) $selectedTahun)
                    ->whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $bidang->id))
                    ->count();

                return [
                    $bidang->slug ?: 'bidang_' . $bidang->id => Tab::make($bidang->nama)
                        ->icon($icon)
                        ->badge($count)
                        ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $bidang->id))),
                ];
            }
        }

        $baseQuery = MasterSubKegiatan::query()->where('tahun_anggaran', (int) $selectedTahun);

        $tabs = [
            'semua' => Tab::make('Semua')
                ->icon('heroicon-m-squares-2x2')
                ->badge($baseQuery->count()),
        ];

        $bidangQuery = Bidang::query()->orderBy('nama');
        $bidangs = $bidangQuery->get();

        foreach ($bidangs as $bidang) {
            $icon = match ($bidang->slug) {
                'sekretariat' => 'heroicon-m-building-office',
                'pemasaran-pariwisata', 'bidang-pemasaran-pariwisata' => 'heroicon-m-megaphone',
                'pengembangan-destinasi-pariwisata' => 'heroicon-m-map-pin',
                'pemuda-dan-olahraga' => 'heroicon-m-trophy',
                default => 'heroicon-m-briefcase',
            };

            $count = MasterSubKegiatan::where('tahun_anggaran', (int) $selectedTahun)
                ->whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $bidang->id))
                ->count();

            $tabs[$bidang->slug ?: 'bidang_' . $bidang->id] = Tab::make($bidang->nama)
                ->icon($icon)
                ->badge($count)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $bidang->id)));
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Sub-Kegiatan')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }
}
