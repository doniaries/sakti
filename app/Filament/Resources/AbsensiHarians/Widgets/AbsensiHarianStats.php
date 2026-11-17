<?php

namespace App\Filament\Resources\AbsensiHarians\Widgets;

use App\Filament\Resources\AbsensiHarians\Pages\ListAbsensiHarians;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AbsensiHarianStats extends BaseWidget
{
    use InteractsWithPageTable;

    // Refresh realtime setiap 15 detik tanpa cache
    protected ?string $pollingInterval = '15s';

    protected function getTablePage(): string
    {
        return ListAbsensiHarians::class;
    }

    protected function getStats(): array
    {
        // Gunakan clone dari getPageTableQuery() yang sudah membawa semua filter aktif dari tabel
        $query = clone $this->getPageTableQuery();

        // Total pegawai aktif di instansi
        $totalPegawaiInstansi = User::query()->where('aktif', true)->count();
        if ($totalPegawaiInstansi === 0) {
            $totalPegawaiInstansi = User::query()->count();
        }

        // Ambil filter tanggal atau bulan aktif jika diset oleh pengguna
        $filterData = $this->tableFilters['tanggal']['tanggal'] ?? null;
        $filterBulan = $this->tableFilters['bulan']['bulan'] ?? null;
        if (!empty($filterData)) {
            $labelPeriode = Carbon::parse($filterData)->translatedFormat('d F Y');
            $tanggalParam = Carbon::parse($filterData)->toDateString();
        } elseif (!empty($filterBulan) && str_contains((string) $filterBulan, '-')) {
            $labelPeriode = Carbon::createFromFormat('Y-m', (string) $filterBulan)->translatedFormat('F Y');
            $tanggalParam = Carbon::today()->toDateString();
        } else {
            $labelPeriode = Carbon::today()->translatedFormat('d F Y');
            $tanggalParam = Carbon::today()->toDateString();
        }

        // Single optimized aggregation query langsung dari query tabel aktif
        // Reset columns dan orders agar kompatibel dengan MySQL strict mode ONLY_FULL_GROUP_BY (Anti SQL Error 1140)
        $aggQuery = (clone $query);
        $aggQuery->getQuery()->columns = null;
        $aggQuery->getQuery()->orders = null;

        $counts = $aggQuery->selectRaw('
            COUNT(CASE WHEN status IN ("hadir", "terlambat") THEN 1 END) as hadir,
            COUNT(CASE WHEN status = "dinas_luar" OR jenis_kehadiran = "dinas_luar" THEN 1 END) as dinas_luar,
            COUNT(CASE WHEN status = "izin" THEN 1 END) as izin,
            COUNT(CASE WHEN status = "cuti" THEN 1 END) as cuti,
            COUNT(CASE WHEN status = "sakit" THEN 1 END) as sakit,
            COUNT(CASE WHEN status = "tidak_hadir" THEN 1 END) as alpa
        ')->first();

        $hadir = (int) ($counts->hadir ?? 0);
        $dinasLuar = (int) ($counts->dinas_luar ?? 0);
        $izin = (int) ($counts->izin ?? 0);
        $cuti = (int) ($counts->cuti ?? 0);
        $sakit = (int) ($counts->sakit ?? 0);
        $alpaDb = (int) ($counts->alpa ?? 0);

        // Sinkronisasi matematis: Jika ada pegawai yang belum absen (belum ada row di absensi_harian hari ini),
        // otomatis dihitung ke Tanpa Keterangan / Belum Hadir agar Total Pas 100% sama dengan Total Pegawai
        $totalTercatat = $hadir + $dinasLuar + $izin + $cuti + $sakit + $alpaDb;
        $belumAbsen = max(0, $totalPegawaiInstansi - $totalTercatat);
        $tanpaKeterangan = $alpaDb + $belumAbsen;

        return [
            Stat::make('Total Pegawai', $totalPegawaiInstansi)
                ->description('Total Pegawai Terdaftar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->url(\App\Filament\Resources\Users\UserResource::getUrl('index')),

            Stat::make('Hadir', $hadir)
                ->description('Hadir / Masuk Kerja')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(ListAbsensiHarians::getUrl([
                    'tableFilters' => [
                        'tanggal' => ['tanggal' => $tanggalParam],
                        'kategori_status' => ['kategori' => 'hadir'],
                    ],
                ])),

            Stat::make('Dinas Luar', $dinasLuar)
                ->description('Tugas Kedinasan Luar')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('info')
                ->url(ListAbsensiHarians::getUrl([
                    'tableFilters' => [
                        'tanggal' => ['tanggal' => $tanggalParam],
                        'kategori_status' => ['kategori' => 'dinas_luar'],
                    ],
                ])),

            Stat::make('Izin & Cuti', $izin + $cuti)
                ->description("Izin: {$izin} | Cuti: {$cuti}")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->url(ListAbsensiHarians::getUrl([
                    'tableFilters' => [
                        'tanggal' => ['tanggal' => $tanggalParam],
                        'kategori_status' => ['kategori' => 'izin'],
                    ],
                ])),

            Stat::make('Sakit', $sakit)
                ->description('Keterangan Surat Dokter')
                ->descriptionIcon('heroicon-m-heart')
                ->color('warning')
                ->url(ListAbsensiHarians::getUrl([
                    'tableFilters' => [
                        'tanggal' => ['tanggal' => $tanggalParam],
                        'kategori_status' => ['kategori' => 'sakit'],
                    ],
                ])),

            Stat::make('Tanpa Keterangan', $tanpaKeterangan)
                ->description($belumAbsen > 0 ? "Belum Tap: {$belumAbsen} | Alpa: {$alpaDb}" : 'Alpa / Tidak Hadir')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color($tanpaKeterangan > 0 ? 'danger' : 'gray')
                ->url(ListAbsensiHarians::getUrl([
                    'tableFilters' => [
                        'tanggal' => ['tanggal' => $tanggalParam],
                        'kategori_status' => ['kategori' => 'tidak_hadir'],
                    ],
                ])),
        ];
    }
}
