<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = User::count();
        $s3Count = User::where('jenjang_pendidikan', 'S3')->count();
        $s2Count = User::where('jenjang_pendidikan', 'S2')->count();
        $s1Count = User::where('jenjang_pendidikan', 'S1')->count();
        $sltaCount = User::where('jenjang_pendidikan', 'SLTA')->count();
        $smpCount = User::where('jenjang_pendidikan', 'SMP')->count();

        return [
            Stat::make('Total Pegawai', $total)
                ->description('Seluruh pegawai dinas')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->url(\App\Filament\Resources\Users\UserResource::getUrl('index')),

            Stat::make('Pendidikan S3', $s3Count)
                ->description('Doktoral')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('purple')
                ->url(\App\Filament\Resources\Users\UserResource::getUrl('index', [
                    'tableFilters' => ['jenjang_pendidikan' => ['value' => 'S3']],
                ])),

            Stat::make('Pendidikan S2', $s2Count)
                ->description('Magister / Pascasarjana')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('danger')
                ->url(\App\Filament\Resources\Users\UserResource::getUrl('index', [
                    'tableFilters' => ['jenjang_pendidikan' => ['value' => 'S2']],
                ])),

            Stat::make('Pendidikan S1', $s1Count)
                ->description('Sarjana / Diploma IV')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success')
                ->url(\App\Filament\Resources\Users\UserResource::getUrl('index', [
                    'tableFilters' => ['jenjang_pendidikan' => ['value' => 'S1']],
                ])),

            Stat::make('Pendidikan SLTA', $sltaCount)
                ->description('SMA / SMK / MA / Sederajat')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning')
                ->url(\App\Filament\Resources\Users\UserResource::getUrl('index', [
                    'tableFilters' => ['jenjang_pendidikan' => ['value' => 'SLTA']],
                ])),

            Stat::make('Pendidikan SMP', $smpCount)
                ->description('SMP / MTs / Sederajat')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info')
                ->url(\App\Filament\Resources\Users\UserResource::getUrl('index', [
                    'tableFilters' => ['jenjang_pendidikan' => ['value' => 'SMP']],
                ])),
        ];
    }
}
