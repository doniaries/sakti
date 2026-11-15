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
        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $canViewAll = $user?->canViewAllBidangKepegawaian() ?? false;

        // Staf / JF biasa: tampilkan tab Data Saya dan tab Pegawai Bidang (tanpa angka jumlah)
        if (! $canViewAll && ! $user?->isPptk()) {
            $tabs = [
                'data_saya' => Tab::make('Data Saya')
                    ->icon('heroicon-m-user')
                    ->modifyQueryUsing(fn(Builder $query) => $query->whereKey($user?->id ?? 0)),
            ];

            if ($user?->bidang_id) {
                $bidang = Bidang::find($user->bidang_id);
                if ($bidang) {
                    $tabs['bidang'] = Tab::make($bidang->nama)
                        ->icon('heroicon-m-user-group')
                        ->modifyQueryUsing(fn(Builder $query) => $query->where('bidang_id', $bidang->id));
                }
            }

            return $tabs;
        }

        // Jika Kepala Bidang / PPTK, HANYA tampilkan tab bidang miliknya saja
        if (! $canViewAll && $user?->bidang_id) {
            $bidang = Bidang::withCount('users')->find($user->bidang_id);
            if ($bidang) {
                $icon = match ($bidang->slug) {
                    'sekretariat' => 'heroicon-m-building-office',
                    'pemasaran-pariwisata', 'bidang-pemasaran-pariwisata' => 'heroicon-m-megaphone',
                    'pengembangan-destinasi-pariwisata' => 'heroicon-m-map-pin',
                    'pemuda-dan-olahraga' => 'heroicon-m-trophy',
                    default => 'heroicon-m-briefcase',
                };

                return [
                    $bidang->slug => Tab::make($bidang->nama)
                        ->icon($icon)
                        ->badge($bidang->users_count)
                        ->badgeColor('primary')
                        ->modifyQueryUsing(fn(Builder $query) => $query->where('bidang_id', $bidang->id)),
                ];
            }
        }

        // Untuk Kasubag Kepegawaian (Admin), PA (Kadis), dan Super Admin: Tampilkan semua
        $tabs = [
            'semua' => Tab::make('Semua Pegawai')
                ->icon('heroicon-m-user-group')
                ->badge(User::query()->bukanSuperAdmin()->count()),
        ];

        $bidangs = Bidang::withCount(['users' => fn($q) => $q->bukanSuperAdmin()])->orderBy('nama')->get();

        foreach ($bidangs as $bidang) {
            $icon = match ($bidang->slug) {
                'sekretariat' => 'heroicon-m-building-office',
                'pemasaran-pariwisata', 'bidang-pemasaran-pariwisata' => 'heroicon-m-megaphone',
                'pengembangan-destinasi-pariwisata' => 'heroicon-m-map-pin',
                'pemuda-dan-olahraga' => 'heroicon-m-trophy',
                default => 'heroicon-m-briefcase',
            };

            $tabs[$bidang->slug] = Tab::make($bidang->nama)
                ->icon($icon)
                ->badge($bidang->users_count)
                ->badgeColor('primary')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('bidang_id', $bidang->id));
        }

        $timKreatifCount = User::query()->whereNotNull('jabatan_tim_kreatif')->bukanSuperAdmin()->count();
        if ($timKreatifCount > 0) {
            $tabs['tim_kreatif'] = Tab::make('Tim Kreatif')
                ->icon('heroicon-m-sparkles')
                ->badge($timKreatifCount)
                ->badgeColor('info')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNotNull('jabatan_tim_kreatif'));
        }

        $geoparkCount = User::query()
            ->whereNull('bidang_id')
            ->whereNotNull('atasan_id')
            ->bukanSuperAdmin()
            ->count();
        if ($geoparkCount > 0) {
            $tabs['geopark'] = Tab::make('Badan Pengelola Geopark')
                ->icon('heroicon-m-globe-alt')
                ->badge($geoparkCount)
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNull('bidang_id')->whereNotNull('atasan_id'));
        }

        $tanpaBidangCount = User::query()
            ->whereNull('bidang_id')
            ->whereNull('atasan_id')
            ->bukanSuperAdmin()
            ->count();
        if ($tanpaBidangCount > 0) {
            $tabs['tanpa_bidang'] = Tab::make('Kepala Dinas')
                ->icon('heroicon-m-user')
                ->badge($tanpaBidangCount)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNull('bidang_id')->whereNull('atasan_id'));
        }

        return $tabs;
    }
}
