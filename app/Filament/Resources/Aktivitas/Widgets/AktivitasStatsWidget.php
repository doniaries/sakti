<?php

namespace App\Filament\Resources\Aktivitas\Widgets;

use App\Models\Aktivitas;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AktivitasStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = Aktivitas::count();
        $global = Aktivitas::whereNull('user_id')->whereNull('jabatan_id')->count();
        $timKreatif = Aktivitas::whereHas('rencanaAksi', fn ($ra) => $ra->where('sasaran_sub_kegiatan', 'Tugas Tambahan'))->count();
        $bidangPegawai = Aktivitas::where(function ($q) {
            $q->whereNotNull('user_id')->orWhereNotNull('jabatan_id');
        })->whereDoesntHave('rencanaAksi', fn ($ra) => $ra->where('sasaran_sub_kegiatan', 'Tugas Tambahan'))->count();

        return [
            Stat::make('Total Aktivitas', number_format($total))
                ->description('Seluruh item di Bank Aktivitas')
                ->color('primary'),
            Stat::make('Aktivitas Global', number_format($global))
                ->description('Dapat dipilih oleh seluruh pegawai')
                ->color('info'),
            Stat::make('Aktivitas Tim Kreatif', number_format($timKreatif))
                ->description('Tugas tambahan Tim Kreatif Disparpora')
                ->color('warning'),
            Stat::make('Aktivitas Bidang / Pegawai', number_format($bidangPegawai))
                ->description('Dikelompokkan per bidang / jabatan / pegawai')
                ->color('success'),
        ];
    }
}
