<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends StatsOverviewWidget
{
    protected int | array | null $columns = [
        'default' => 2,
        'sm' => 3,
        'md' => 6,
        'lg' => 6,
        'xl' => 6,
    ];

    protected function getStats(): array
    {
        $baseQuery = User::query()->bukanSuperAdmin();

        $total = (clone $baseQuery)->count();
        $s3Count = (clone $baseQuery)->where('jenjang_pendidikan', '=', 'S3')->count();
        $s2Count = (clone $baseQuery)->where('jenjang_pendidikan', '=', 'S2')->count();
        $s1Count = (clone $baseQuery)->where('jenjang_pendidikan', '=', 'S1')->count();
        $sltaCount = (clone $baseQuery)->where('jenjang_pendidikan', '=', 'SLTA')->count();
        $smpCount = (clone $baseQuery)->where('jenjang_pendidikan', '=', 'SMP')->count();

        return [
            Stat::make('Total Pegawai', $total)
                ->description('Semua Pegawai')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->url(\App\Filament\Resources\Users\UserResource::getUrl('index')),

            Stat::make('Pendidikan S3', $s3Count)
                ->description('Doktoral')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('purple')
                ->url(\App\Filament\Resources\Users\UserResource::getUrl('index', [
                    'tableFilters' => ['jenjang_pendidikan' => ['value' => 'S3']],
                    'jenjang' => 'S3',
                ])),

            Stat::make('Pendidikan S2', $s2Count)
                ->description('Magister')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('danger')
                ->url(\App\Filament\Resources\Users\UserResource::getUrl('index', [
                    'tableFilters' => ['jenjang_pendidikan' => ['value' => 'S2']],
                    'jenjang' => 'S2',
                ])),

            Stat::make('Pendidikan S1', $s1Count)
                ->description('Sarjana')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success')
                ->url(\App\Filament\Resources\Users\UserResource::getUrl('index', [
                    'tableFilters' => ['jenjang_pendidikan' => ['value' => 'S1']],
                    'jenjang' => 'S1',
                ])),

            Stat::make('Pendidikan SLTA', $sltaCount)
                ->description('SMA / SMK')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning')
                ->url(\App\Filament\Resources\Users\UserResource::getUrl('index', [
                    'tableFilters' => ['jenjang_pendidikan' => ['value' => 'SLTA']],
                    'jenjang' => 'SLTA',
                ])),

            Stat::make('Pendidikan SMP', $smpCount)
                ->description('SMP / MTs')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info')
                ->url(\App\Filament\Resources\Users\UserResource::getUrl('index', [
                    'tableFilters' => ['jenjang_pendidikan' => ['value' => 'SMP']],
                    'jenjang' => 'SMP',
                ])),
        ];
    }
}
