<?php

namespace App\Filament\Resources\AbsensiHarians\Pages;

use App\Filament\Resources\AbsensiHarians\AbsensiHarianResource;
use App\Models\AbsensiHarian;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAbsensiHarians extends ListRecords
{
    protected static string $resource = AbsensiHarianResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\AbsensiHarians\Widgets\AbsensiHarianStats::class,
        ];
    }

    public function getWidgetData(): array
    {
        $data = parent::getWidgetData();
        
        $data['tableColumnSearches'] = $data['tableColumnSearches'] ?? [];
        $data['tableFilters'] = $data['tableFilters'] ?? [];
        $data['tableSearch'] = $data['tableSearch'] ?? '';
        
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /**
     * Tab Filter Cepat Status Kehadiran Pegawai
     * - Semua
     * - Lengkap (Masuk & Pulang)
     * - Belum Pulang (Hanya Masuk) -> Tempat aksi 1-klik tutup absen
     * - Dinas Luar / Izin
     * - Tidak Hadir / Alpa
     */
    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Data')
                ->icon('heroicon-m-clipboard-document-list'),

            'lengkap' => Tab::make('Lengkap (Masuk & Pulang)')
                ->icon('heroicon-m-check-badge')
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('jam_masuk')->whereNotNull('jam_pulang')),

            'belum_pulang' => Tab::make('Belum Pulang')
                ->icon('heroicon-m-clock')
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('jam_masuk')->whereNull('jam_pulang')->whereNotIn('status', ['tidak_hadir', 'izin', 'sakit'])),

            'dinas_luar' => Tab::make('Dinas Luar / Izin')
                ->icon('heroicon-m-briefcase')
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where(fn ($q) => $q->where('jenis_kehadiran', 'dinas_luar')->orWhereIn('status', ['dinas_luar', 'izin', 'sakit', 'cuti']))),

            'tidak_hadir' => Tab::make('Tidak Hadir / Alpa')
                ->icon('heroicon-m-x-circle')
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'tidak_hadir')->orWhere(fn ($q) => $q->whereNull('jam_masuk')->whereNull('jam_pulang'))),
        ];
    }
}
