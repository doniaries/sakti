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

        // Ambil filter bidang aktif dari request / referer
        $selectedBidang = request()->query('bidang')
            ?? data_get(request()->query('filters'), 'bidang_id.value')
            ?? data_get(request()->query('tableFilters'), 'bidang_id.value');

        $selectedBulan = request()->query('bulan');
        $selectedTanggal = request()->query('tanggal');

        if ((blank($selectedBidang) || blank($selectedBulan) || blank($selectedTanggal)) && request()->header('Referer')) {
            $refererQuery = [];
            parse_str(parse_url(request()->header('Referer'), PHP_URL_QUERY) ?? '', $refererQuery);
            $selectedBidang = $selectedBidang ?? ($refererQuery['bidang']
                ?? data_get($refererQuery, 'filters.bidang_id.value')
                ?? data_get($refererQuery, 'tableFilters.bidang_id.value'));
            $selectedBulan = $selectedBulan ?? ($refererQuery['bulan'] ?? null);
            $selectedTanggal = $selectedTanggal ?? ($refererQuery['tanggal'] ?? null);
        }

        $tahunAktif = \App\Models\PengaturanAplikasi::getTahunAnggaranAktif();

        // Base query yang konsisten mengunci tahun anggaran, filter bidang, bulan, tanggal, dan hak akses otorisasi role
        $baseQuery = function () use ($user, $tahunAktif, $selectedBidang, $selectedBulan, $selectedTanggal) {
            $query = PengajuanSpj::query()
                ->where('tahun_anggaran', $tahunAktif)
                ->when(filled($selectedBidang), fn ($q) => $q->where('bidang_id', (int) $selectedBidang))
                ->when(filled($selectedBulan), fn ($q) => $q->whereMonth('tanggal_pengajuan', (int) $selectedBulan))
                ->when(filled($selectedTanggal), fn ($q) => $q->whereDate('tanggal_pengajuan', $selectedTanggal));

            if (! $user) {
                return $query;
            }

            // PPK, PA, Bendahara, Super Admin, Admin Keuangan dapat melihat seluruh bidang dinas
            if ($user->canViewAllBidangKeuangan()) {
                return $query;
            }

            // PPTK dapat melihat seluruh SPJ di bidangnya + SPJ miliknya
            if ($user->isPptk()) {
                $userBidangId = (int) ($user->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $user->id)->value('id') ?? 0));
                return $query->where(function (Builder $q) use ($user, $userBidangId) {
                    if ($userBidangId > 0) {
                        $q->where('bidang_id', $userBidangId);
                    }
                    $q->orWhere('user_id', $user->id);
                });
            }

            // JF / Staf biasa hanya menghitung SPJ yang terkait dengannya (dibuat olehnya)
            return $query->where('user_id', $user->id);
        };

        // Hitung badge antrean khusus tugas user aktif
        $antreanSayaQuery = $baseQuery();
        if ($user) {
            if ($user->hasAnyRole(['super_admin', 'admin'])) {
                $antreanSayaQuery->whereIn('status', [
                    'draft',
                    'diajukan',
                    'menunggu_realisasi',
                    'menunggu_verifikasi_pptk',
                    'menunggu_verifikasi_ppk',
                    'menunggu_persetujuan_pa',
                    'menunggu_verifikasi_bendahara',
                    'revisi_pptk',
                    'revisi_ppk',
                    'revisi_pa',
                    'revisi_bendahara',
                ]);
            } elseif ($user->isPptk()) {
                $antreanSayaQuery->where(function (Builder $sub) use ($user) {
                    $sub->where(function (Builder $pptk) use ($user) {
                        $pptk->whereIn('status', ['diajukan', 'menunggu_verifikasi_pptk']);
                        if (filled($user->bidang_id)) {
                            $pptk->where('bidang_id', $user->bidang_id);
                        }
                    })->orWhere(function (Builder $own) use ($user) {
                        $own->where('user_id', $user->id);
                    });
                });
            } elseif ($user->hasRole('ppk') || $anggaranRole === 'PPK') {
                $antreanSayaQuery->where(function (Builder $sub) use ($user) {
                    $sub->where('status', 'menunggu_verifikasi_ppk')
                        ->orWhere(function (Builder $own) use ($user) {
                            $own->where('user_id', $user->id)
                                ->whereIn('status', ['draft', 'menunggu_realisasi', 'revisi_pptk', 'revisi_ppk', 'revisi_pa', 'revisi_bendahara']);
                        });
                });
            } elseif ($user->hasRole('pa') || $anggaranRole === 'PA') {
                $antreanSayaQuery->where(function (Builder $sub) use ($user) {
                    $sub->where('status', 'menunggu_persetujuan_pa')
                        ->orWhere(function (Builder $own) use ($user) {
                            $own->where('user_id', $user->id)
                                ->whereIn('status', ['draft', 'menunggu_realisasi', 'revisi_pptk', 'revisi_ppk', 'revisi_pa', 'revisi_bendahara']);
                        });
                });
            } elseif ($user->hasRole('bendahara') || $anggaranRole === 'BENDAHARA') {
                $antreanSayaQuery->where(function (Builder $sub) use ($user) {
                    $sub->where('status', 'menunggu_verifikasi_bendahara')
                        ->orWhere(function (Builder $own) use ($user) {
                            $own->where('user_id', $user->id)
                                ->whereIn('status', ['draft', 'menunggu_realisasi', 'revisi_pptk', 'revisi_ppk', 'revisi_pa', 'revisi_bendahara']);
                        });
                });
            } else {
                // Untuk JF / Staf: tampilkan semua SPJ miliknya tanpa tersembunyi saat status berganti
                $antreanSayaQuery->where('user_id', $user->id);
            }
        }
        $antreanSayaCount = $antreanSayaQuery->count();

        // Hitung total berkas per tahapan dalam 1 single grouped query (Anti N+1)
        $statusCounts = $baseQuery()
            ->groupBy('status')
            ->selectRaw('status, count(*) as aggregate')
            ->pluck('aggregate', 'status');

        $draftCount = (int) ($statusCounts['draft'] ?? 0);
        $semuaCount = (int) $statusCounts->sum();
        $ppkCount = (int) ($statusCounts['menunggu_verifikasi_ppk'] ?? 0);
        $paCount = (int) ($statusCounts['menunggu_persetujuan_pa'] ?? 0);
        $bendaharaCount = (int) ($statusCounts['menunggu_verifikasi_bendahara'] ?? 0);
        $revisiCount = (int) (($statusCounts['revisi_pptk'] ?? 0) + ($statusCounts['revisi_ppk'] ?? 0) + ($statusCounts['revisi_pa'] ?? 0) + ($statusCounts['revisi_bendahara'] ?? 0));
        $cairCount = (int) ($statusCounts['dicairkan'] ?? 0);

        $labelAntreanSaya = match (true) {
            $user?->hasAnyRole(['super_admin', 'admin']) => 'Semua Antrean & Draft',
            $user?->hasRole('ppk') || $anggaranRole === 'PPK' => 'Perlu Diperiksa PPK',
            $user?->hasRole('pa') || $anggaranRole === 'PA' => 'Perlu Persetujuan PA',
            $user?->hasRole('bendahara') || $anggaranRole === 'BENDAHARA' => 'Perlu Uji Kas Bendahara',
            $user?->isPptk() => 'Antrean PPTK & Berkas Saya',
            default => 'Pengajuan & SPJ Saya',
        };

        return [
            'antrean_saya' => Tab::make($labelAntreanSaya)
                ->badge($antreanSayaCount)
                ->badgeColor($antreanSayaCount > 0 ? 'danger' : 'gray')
                ->modifyQueryUsing(function (Builder $query) use ($user, $anggaranRole, $tahunAktif, $selectedBidang, $selectedBulan, $selectedTanggal) {
                    $query->where('tahun_anggaran', $tahunAktif);
                    if (filled($selectedBidang)) {
                        $query->where('bidang_id', (int) $selectedBidang);
                    }
                    if (filled($selectedBulan)) {
                        $query->whereMonth('tanggal_pengajuan', (int) $selectedBulan);
                    }
                    if (filled($selectedTanggal)) {
                        $query->whereDate('tanggal_pengajuan', $selectedTanggal);
                    }
                    if (! $user) return;
                    if ($user->hasAnyRole(['super_admin', 'admin'])) {
                        $query->whereIn('status', [
                            'draft',
                            'diajukan',
                            'menunggu_realisasi',
                            'menunggu_verifikasi_pptk',
                            'menunggu_verifikasi_ppk',
                            'menunggu_persetujuan_pa',
                            'menunggu_verifikasi_bendahara',
                            'revisi_pptk',
                            'revisi_ppk',
                            'revisi_pa',
                            'revisi_bendahara',
                        ]);
                    } elseif ($user->isPptk()) {
                        $query->where(function (Builder $sub) use ($user) {
                            $sub->where(function (Builder $pptk) use ($user) {
                                $pptk->whereIn('status', ['diajukan', 'menunggu_verifikasi_pptk']);
                                if (filled($user->bidang_id)) {
                                    $pptk->where('bidang_id', $user->bidang_id);
                                }
                            })->orWhere(function (Builder $own) use ($user) {
                                $own->where('user_id', $user->id);
                            });
                        });
                    } elseif ($user->hasRole('ppk') || $anggaranRole === 'PPK') {
                        $query->where(function (Builder $sub) use ($user) {
                            $sub->where('status', 'menunggu_verifikasi_ppk')
                                ->orWhere(function (Builder $own) use ($user) {
                                    $own->where('user_id', $user->id)
                                        ->whereIn('status', ['draft', 'menunggu_realisasi', 'revisi_pptk', 'revisi_ppk', 'revisi_pa', 'revisi_bendahara']);
                                });
                        });
                    } elseif ($user->hasRole('pa') || $anggaranRole === 'PA') {
                        $query->where(function (Builder $sub) use ($user) {
                            $sub->where('status', 'menunggu_persetujuan_pa')
                                ->orWhere(function (Builder $own) use ($user) {
                                    $own->where('user_id', $user->id)
                                        ->whereIn('status', ['draft', 'menunggu_realisasi', 'revisi_pptk', 'revisi_ppk', 'revisi_pa', 'revisi_bendahara']);
                                });
                        });
                    } elseif ($user->hasRole('bendahara') || $anggaranRole === 'BENDAHARA') {
                        $query->where(function (Builder $sub) use ($user) {
                            $sub->where('status', 'menunggu_verifikasi_bendahara')
                                ->orWhere(function (Builder $own) use ($user) {
                                    $own->where('user_id', $user->id)
                                        ->whereIn('status', ['draft', 'menunggu_realisasi', 'revisi_pptk', 'revisi_ppk', 'revisi_pa', 'revisi_bendahara']);
                                });
                        });
                    } else {
                        // JF / Staf: tampilkan semua SPJ miliknya tanpa hilang saat status berubah
                        $query->where('user_id', $user->id);
                    }
                }),

            'draft' => Tab::make('Draft Baru')
                ->badge($draftCount)
                ->badgeColor($draftCount > 0 ? 'warning' : 'gray')
                ->modifyQueryUsing(function (Builder $query) use ($tahunAktif, $selectedBidang, $selectedBulan, $selectedTanggal) {
                    $query->where('tahun_anggaran', $tahunAktif)->where('status', 'draft');
                    if (filled($selectedBidang)) {
                        $query->where('bidang_id', (int) $selectedBidang);
                    }
                    if (filled($selectedBulan)) {
                        $query->whereMonth('tanggal_pengajuan', (int) $selectedBulan);
                    }
                    if (filled($selectedTanggal)) {
                        $query->whereDate('tanggal_pengajuan', $selectedTanggal);
                    }
                }),

            'semua' => Tab::make('Semua SPJ')
                ->badge($semuaCount)
                ->badgeColor($semuaCount > 0 ? 'primary' : 'gray')
                ->modifyQueryUsing(function (Builder $query) use ($tahunAktif, $selectedBidang, $selectedBulan, $selectedTanggal) {
                    $query->where('tahun_anggaran', $tahunAktif);
                    if (filled($selectedBidang)) {
                        $query->where('bidang_id', (int) $selectedBidang);
                    }
                    if (filled($selectedBulan)) {
                        $query->whereMonth('tanggal_pengajuan', (int) $selectedBulan);
                    }
                    if (filled($selectedTanggal)) {
                        $query->whereDate('tanggal_pengajuan', $selectedTanggal);
                    }
                }),

            'menunggu_ppk' => Tab::make('Menunggu PPK')
                ->badge($ppkCount)
                ->badgeColor($ppkCount > 0 ? 'warning' : 'gray')
                ->modifyQueryUsing(function (Builder $query) use ($tahunAktif, $selectedBidang, $selectedBulan, $selectedTanggal) {
                    $query->where('tahun_anggaran', $tahunAktif)->where('status', 'menunggu_verifikasi_ppk');
                    if (filled($selectedBidang)) {
                        $query->where('bidang_id', (int) $selectedBidang);
                    }
                    if (filled($selectedBulan)) {
                        $query->whereMonth('tanggal_pengajuan', (int) $selectedBulan);
                    }
                    if (filled($selectedTanggal)) {
                        $query->whereDate('tanggal_pengajuan', $selectedTanggal);
                    }
                }),

            'menunggu_pa' => Tab::make('Menunggu PA')
                ->badge($paCount)
                ->badgeColor($paCount > 0 ? 'info' : 'gray')
                ->modifyQueryUsing(function (Builder $query) use ($tahunAktif, $selectedBidang, $selectedBulan, $selectedTanggal) {
                    $query->where('tahun_anggaran', $tahunAktif)->where('status', 'menunggu_persetujuan_pa');
                    if (filled($selectedBidang)) {
                        $query->where('bidang_id', (int) $selectedBidang);
                    }
                    if (filled($selectedBulan)) {
                        $query->whereMonth('tanggal_pengajuan', (int) $selectedBulan);
                    }
                    if (filled($selectedTanggal)) {
                        $query->whereDate('tanggal_pengajuan', $selectedTanggal);
                    }
                }),

            'menunggu_bendahara' => Tab::make('Menunggu Bendahara')
                ->badge($bendaharaCount)
                ->badgeColor($bendaharaCount > 0 ? 'primary' : 'gray')
                ->modifyQueryUsing(function (Builder $query) use ($tahunAktif, $selectedBidang, $selectedBulan, $selectedTanggal) {
                    $query->where('tahun_anggaran', $tahunAktif)->where('status', 'menunggu_verifikasi_bendahara');
                    if (filled($selectedBidang)) {
                        $query->where('bidang_id', (int) $selectedBidang);
                    }
                    if (filled($selectedBulan)) {
                        $query->whereMonth('tanggal_pengajuan', (int) $selectedBulan);
                    }
                    if (filled($selectedTanggal)) {
                        $query->whereDate('tanggal_pengajuan', $selectedTanggal);
                    }
                }),

            'perlu_revisi' => Tab::make('Perlu Revisi')
                ->badge($revisiCount)
                ->badgeColor($revisiCount > 0 ? 'danger' : 'gray')
                ->modifyQueryUsing(function (Builder $query) use ($tahunAktif, $selectedBidang, $selectedBulan, $selectedTanggal) {
                    $query->where('tahun_anggaran', $tahunAktif)->whereIn('status', ['revisi_pptk', 'revisi_ppk', 'revisi_pa', 'revisi_bendahara']);
                    if (filled($selectedBidang)) {
                        $query->where('bidang_id', (int) $selectedBidang);
                    }
                    if (filled($selectedBulan)) {
                        $query->whereMonth('tanggal_pengajuan', (int) $selectedBulan);
                    }
                    if (filled($selectedTanggal)) {
                        $query->whereDate('tanggal_pengajuan', $selectedTanggal);
                    }
                }),

            'selesai' => Tab::make('Selesai / Cair')
                ->badge($cairCount)
                ->badgeColor('success')
                ->modifyQueryUsing(function (Builder $query) use ($tahunAktif, $selectedBidang, $selectedBulan, $selectedTanggal) {
                    $query->where('tahun_anggaran', $tahunAktif)->where('status', 'dicairkan');
                    if (filled($selectedBidang)) {
                        $query->where('bidang_id', (int) $selectedBidang);
                    }
                    if (filled($selectedBulan)) {
                        $query->whereMonth('tanggal_pengajuan', (int) $selectedBulan);
                    }
                    if (filled($selectedTanggal)) {
                        $query->whereDate('tanggal_pengajuan', $selectedTanggal);
                    }
                }),
        ];
    }
}
