<?php

namespace App\Filament\Resources\PengajuanSpjs\Pages;

use App\Filament\Resources\KodeRekenings\KodeRekeningResource;
use App\Filament\Resources\MasterBarangs\MasterBarangResource;
use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Filament\Resources\PengajuanSpjs\Widgets\KeuanganBidangOverviewWidget;
use App\Filament\Resources\StokBarangs\StokBarangResource;
use App\Models\PengajuanSpj;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use ZeeshanTariq\FilamentStickyColumns\Concerns\InteractsWithStickyableColumns;

class ListPengajuanSpjs extends ListRecords
{
    use InteractsWithStickyableColumns;

    protected static string $resource = PengajuanSpjResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            KeuanganBidangOverviewWidget::class,
        ];
    }

    public function getTabs(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $anggaranRole = $user?->anggaran_role instanceof \App\Enums\AnggaranRole
            ? $user->anggaran_role->value
            : $user?->anggaran_role;

        // Hitung badge antrean khusus tugas user aktif
        $antreanSayaQuery = PengajuanSpj::query();
        if ($user) {
            if ($user->hasRole('ppk') || $anggaranRole === 'PPK') {
                $antreanSayaQuery->where('status', 'menunggu_verifikasi_ppk');
            } elseif ($user->hasRole('pa') || $anggaranRole === 'PA') {
                $antreanSayaQuery->where('status', 'menunggu_persetujuan_pa');
            } elseif ($user->hasRole('bendahara') || $anggaranRole === 'BENDAHARA') {
                $antreanSayaQuery->where('status', 'menunggu_verifikasi_bendahara');
            } elseif ($user->hasRole('pptk') || $anggaranRole === 'PPTK') {
                $antreanSayaQuery->whereIn('status', ['diajukan', 'menunggu_verifikasi_pptk']);
                if (filled($user->bidang_id)) {
                    $antreanSayaQuery->where('bidang_id', $user->bidang_id);
                }
            } elseif ($user->hasAnyRole(['super_admin', 'admin'])) {
                $antreanSayaQuery->whereIn('status', [
                    'diajukan',
                    'menunggu_verifikasi_pptk',
                    'menunggu_verifikasi_ppk',
                    'menunggu_persetujuan_pa',
                    'menunggu_verifikasi_bendahara',
                ]);
            } else {
                $antreanSayaQuery->where('user_id', $user->id)
                    ->whereIn('status', ['draft', 'revisi_pptk', 'revisi_ppk', 'revisi_pa', 'revisi_bendahara']);
            }
        }
        $antreanSayaCount = $antreanSayaQuery->count();

        // Hitung total berkas per tahapan untuk badge tab
        $ppkCount = PengajuanSpj::query()->where('status', 'menunggu_verifikasi_ppk')->count();
        $paCount = PengajuanSpj::query()->where('status', 'menunggu_persetujuan_pa')->count();
        $bendaharaCount = PengajuanSpj::query()->where('status', 'menunggu_verifikasi_bendahara')->count();
        $revisiCount = PengajuanSpj::query()->whereIn('status', ['revisi_pptk', 'revisi_ppk', 'revisi_pa', 'revisi_bendahara'])->count();
        $cairCount = PengajuanSpj::query()->where('status', 'dicairkan')->count();

        $labelAntreanSaya = match (true) {
            $user?->hasRole('ppk') || $anggaranRole === 'PPK' => 'Perlu Diperiksa PPK',
            $user?->hasRole('pa') || $anggaranRole === 'PA' => 'Perlu Persetujuan PA',
            $user?->hasRole('bendahara') || $anggaranRole === 'BENDAHARA' => 'Perlu Uji Kas Bendahara',
            $user?->hasRole('pptk') || $anggaranRole === 'PPTK' => 'Perlu Verifikasi PPTK',
            $user?->hasAnyRole(['super_admin', 'admin']) => 'Semua Antrean Verifikasi',
            default => 'Tugas & Draft Saya',
        };

        return [
            'antrean_saya' => Tab::make($labelAntreanSaya)
                ->badge($antreanSayaCount)
                ->badgeColor($antreanSayaCount > 0 ? 'danger' : 'gray')
                ->modifyQueryUsing(function (Builder $query) use ($user, $anggaranRole) {
                    if (! $user) return;
                    if ($user->hasRole('ppk') || $anggaranRole === 'PPK') {
                        $query->where('status', 'menunggu_verifikasi_ppk');
                    } elseif ($user->hasRole('pa') || $anggaranRole === 'PA') {
                        $query->where('status', 'menunggu_persetujuan_pa');
                    } elseif ($user->hasRole('bendahara') || $anggaranRole === 'BENDAHARA') {
                        $query->where('status', 'menunggu_verifikasi_bendahara');
                    } elseif ($user->hasRole('pptk') || $anggaranRole === 'PPTK') {
                        $query->whereIn('status', ['diajukan', 'menunggu_verifikasi_pptk']);
                        if (filled($user->bidang_id)) {
                            $query->where('bidang_id', $user->bidang_id);
                        }
                    } elseif ($user->hasAnyRole(['super_admin', 'admin'])) {
                        $query->whereIn('status', [
                            'diajukan',
                            'menunggu_verifikasi_pptk',
                            'menunggu_verifikasi_ppk',
                            'menunggu_persetujuan_pa',
                            'menunggu_verifikasi_bendahara',
                        ]);
                    } else {
                        $query->where('user_id', $user->id)
                            ->whereIn('status', ['draft', 'revisi_pptk', 'revisi_ppk', 'revisi_pa', 'revisi_bendahara']);
                    }
                }),

            'semua' => Tab::make('Semua SPJ')
                ->badge(PengajuanSpj::count())
                ->badgeColor('gray'),

            'menunggu_ppk' => Tab::make('Menunggu PPK')
                ->badge($ppkCount)
                ->badgeColor($ppkCount > 0 ? 'warning' : 'gray')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'menunggu_verifikasi_ppk')),

            'menunggu_pa' => Tab::make('Menunggu PA')
                ->badge($paCount)
                ->badgeColor($paCount > 0 ? 'info' : 'gray')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'menunggu_persetujuan_pa')),

            'menunggu_bendahara' => Tab::make('Menunggu Bendahara')
                ->badge($bendaharaCount)
                ->badgeColor($bendaharaCount > 0 ? 'primary' : 'gray')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'menunggu_verifikasi_bendahara')),

            'perlu_revisi' => Tab::make('Perlu Revisi')
                ->badge($revisiCount)
                ->badgeColor($revisiCount > 0 ? 'danger' : 'gray')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('status', ['revisi_pptk', 'revisi_ppk', 'revisi_pa', 'revisi_bendahara'])),

            'selesai' => Tab::make('Selesai / Cair')
                ->badge($cairCount)
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'dicairkan')),
        ];
    }
}
