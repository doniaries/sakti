<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Bidangs\BidangResource;
use App\Filament\Resources\Jabatans\JabatanResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Bidang;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use ZeeshanTariq\FilamentStickyColumns\Concerns\InteractsWithStickyableColumns;

class ListUsers extends ListRecords
{
    use InteractsWithStickyableColumns;

    protected static string $resource = UserResource::class;

    /**
     * Tombol aksi telah dipindahkan ke Table Header Actions (UsersTable)
     * agar sejajar dengan toolbar tabel di bawah tab filter.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\Users\Widgets\UserStatsWidget::class,
        ];
    }

    /**
     * Filter Tab Bidang untuk memudahkan monitoring pegawai, pengaturan WFH/WFO, dan jadwal kerja.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $tabs = [
            'semua' => Tab::make('Semua Pegawai')
                ->icon('heroicon-m-user-group')
                ->badge(User::count()),
        ];

        $bidangs = Bidang::withCount('users')->orderBy('nama')->get();

        foreach ($bidangs as $bidang) {
            $icon = match ($bidang->slug) {
                'sekretariat' => 'heroicon-m-building-office',
                'bidang-pemasaran-pariwisata' => 'heroicon-m-megaphone',
                'pengembangan-destinasi-pariwisata' => 'heroicon-m-map-pin',
                'pemuda-dan-olahraga' => 'heroicon-m-trophy',
                default => 'heroicon-m-briefcase',
            };

            $tabs[$bidang->slug] = Tab::make($bidang->nama)
                ->icon($icon)
                ->badge($bidang->users_count)
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('bidang_id', $bidang->id));
        }

        $tanpaBidangCount = User::whereNull('bidang_id')->count();
        if ($tanpaBidangCount > 0) {
            $tabs['tanpa_bidang'] = Tab::make('Pimpinan / Tanpa Bidang')
                ->icon('heroicon-m-user')
                ->badge($tanpaBidangCount)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('bidang_id'));
        }

        return $tabs;
    }
}
