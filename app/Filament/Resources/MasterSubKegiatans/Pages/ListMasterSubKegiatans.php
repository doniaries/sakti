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
        $isGlobal = ! $user || $user->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara', 'pimpinan']);

        $baseQuery = MasterSubKegiatan::query();
        if (! $isGlobal && $user->bidang_id) {
            $baseQuery->whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $user->bidang_id));
        }

        $tabs = [
            'semua' => Tab::make('Semua')
                ->icon('heroicon-m-squares-2x2')
                ->badge($baseQuery->count()),
        ];

        $bidangQuery = Bidang::query()->orderBy('nama');
        if (! $isGlobal && $user->bidang_id) {
            $bidangQuery->where('id', $user->bidang_id);
        }
        $bidangs = $bidangQuery->get();

        foreach ($bidangs as $bidang) {
            $icon = match ($bidang->slug) {
                'sekretariat' => 'heroicon-m-building-office',
                'bidang-pemasaran-pariwisata' => 'heroicon-m-megaphone',
                'pengembangan-destinasi-pariwisata' => 'heroicon-m-map-pin',
                'pemuda-dan-olahraga' => 'heroicon-m-trophy',
                default => 'heroicon-m-briefcase',
            };

            $count = MasterSubKegiatan::whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $bidang->id))->count();

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

            Action::make('kelola_kegiatan')
                ->label('Kelola Kegiatan')
                ->icon('heroicon-o-queue-list')
                ->color('gray')
                ->url(fn (): string => MasterKegiatanResource::getUrl('index')),

            Action::make('kelola_program')
                ->label('Kelola Program')
                ->icon('heroicon-o-folder')
                ->color('gray')
                ->url(fn (): string => MasterProgramResource::getUrl('index')),

            Action::make('kelola_rekening')
                ->label('Kode Rekening')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('gray')
                ->url(fn (): string => KodeRekeningResource::getUrl('index')),

            Action::make('rka_belanja')
                ->label('Rincian Belanja (RKA)')
                ->icon('heroicon-o-document-chart-bar')
                ->color('gray')
                ->url(fn (): string => \App\Filament\Resources\RincianAnggaranBelanjas\RincianAnggaranBelanjaResource::getUrl('index')),

            Action::make('kelola_spj')
                ->label('SPJ Belanja')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('gray')
                ->url(fn (): string => PengajuanSpjResource::getUrl('index')),
        ];
    }
}
