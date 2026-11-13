<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class UserStatsWidget extends StatsOverviewWidget
{
    protected function getColumns(): int | array
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user && ! $user->canViewAllBidangKepegawaian() && ! $user->isPptk()) {
            return [
                'default' => 2,
                'md' => 4,
            ];
        }

        return [
            'default' => 2,
            'sm' => 3,
            'md' => 6,
            'lg' => 6,
            'xl' => 6,
        ];
    }

    protected function getStats(): array
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user && ! $user->canViewAllBidangKepegawaian() && ! $user->isPptk()) {
            $user->loadMissing(['jabatan', 'bidang', 'atasan', 'jfPenempatan']);
            $viewUrl = UserResource::getUrl('view', ['record' => $user]);
            $golongan = trim(($user->pangkat ?? '') . ($user->golongan ? " ({$user->golongan})" : ''));

            $rekanBidang = $user->bidang_id
                ? User::query()
                    ->where('bidang_id', '=', $user->bidang_id)
                    ->bukanSuperAdmin()
                    ->orderBy('name')
                    ->pluck('name')
                    ->implode(', ')
                : '-';

            return [
                Stat::make('Data Pegawai Anda', $user->nip ?: '-')
                    ->description($user->name)
                    ->descriptionIcon('heroicon-m-identification')
                    ->color('primary')
                    ->url($viewUrl),

                Stat::make('Jabatan & Unit Kerja', $user->jabatan?->nama ?? '-')
                    ->description(collect([$user->bidang?->nama, $user->jfPenempatan ? 'JF: ' . $user->jfPenempatan->name : null])->filter()->implode(' • ') ?: '-')
                    ->descriptionIcon('heroicon-m-building-office')
                    ->color('info')
                    ->url($viewUrl),

                Stat::make('Pendidikan & Golongan', $user->jenjang_pendidikan ?: '-')
                    ->description($golongan !== '' ? $golongan : ($user->jurusan ?: 'Belum dilengkapi'))
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('success')
                    ->url($viewUrl),

                Stat::make('Pegawai di Bidang', $user->bidang?->nama ?? '-')
                    ->description($rekanBidang ?: '-')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('warning'),
            ];
        }

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
