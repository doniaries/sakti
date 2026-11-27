<?php

namespace App\Livewire\Mobile;

use App\Models\AbsensiHarian;
use App\Models\Lhk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Riwayat Absensi - Absensi LHK Mobile')]
class Riwayat extends Component
{
    public int $bulan;
    public int $tahun;

    #[Url(as: 'status')]
    public string $filterStatus = 'semua';

    public string $filterTanggal = '';

    public ?AbsensiHarian $selectedAbsensi = null;
    public bool $showDetailModal = false;
    public bool $showCetakModal = false;
    public int $cetakBulan = 1;
    public int $cetakTahun = 2026;
    public string $cetakStatus = 'semua';

    public function openCetak(): void
    {
        $this->cetakBulan = $this->bulan;
        $this->cetakTahun = $this->tahun;
        $this->cetakStatus = $this->filterStatus ?: 'semua';
        $this->showCetakModal = true;
    }

    public function bukaDetail(int $id): void
    {
        $this->selectedAbsensi = AbsensiHarian::with(['logMasuk', 'logPulang', 'user'])->find($id);
        $this->showDetailModal = true;
    }

    public function tutupDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedAbsensi = null;
    }

    public function mount()
    {
        $this->bulan = request()->query('bulan', date('m'));
        $this->tahun = request()->query('tahun', date('Y'));
        
        if (request()->has('status')) {
            $this->filterStatus = request()->query('status');
        }

        $this->cetakBulan = $this->bulan;
        $this->cetakTahun = $this->tahun;
        $this->cetakStatus = $this->filterStatus ?: 'semua';
    }

    public function prevMonth()
    {
        $date = Carbon::createFromDate($this->tahun, $this->bulan, 1)->subMonth();
        $this->bulan = $date->format('m');
        $this->tahun = $date->format('Y');
    }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->tahun, $this->bulan, 1)->addMonth();
        $this->bulan = $date->format('m');
        $this->tahun = $date->format('Y');
    }

    public function setFilter($status)
    {
        $this->filterStatus = $status;
    }

    public function render()
    {
        $userId = Auth::id();

        // Ambil semua absensi di bulan bersangkutan untuk rekap card
        $allBulanIni = AbsensiHarian::where('user_id', $userId)
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->get();

        $rekap = [
            'total' => $allBulanIni->count(),
            'hadir' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['hadir', 'terlambat']))->count(),
            'terlambat' => $allBulanIni->filter(fn($a) => ($a->status?->value ?? $a->status) === 'terlambat')->count(),
            'dinas_luar' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['dinas_luar', 'dl']))->count(),
            'izin_sakit' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['izin', 'sakit']))->count(),
            'cuti' => $allBulanIni->filter(fn($a) => ($a->status?->value ?? $a->status) === 'cuti')->count(),
            'alpa' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['tidak_hadir', 'alpa']))->count(),
        ];

        // Query berdasarkan filter aktif
        $query = AbsensiHarian::where('user_id', $userId)
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->with(['logMasuk', 'logPulang']);

        if ($this->filterStatus && $this->filterStatus !== 'semua') {
            if ($this->filterStatus === 'hadir') {
                $query->whereIn('status', ['hadir', 'terlambat']);
            } elseif ($this->filterStatus === 'izin_sakit') {
                $query->whereIn('status', ['izin', 'sakit']);
            } elseif ($this->filterStatus === 'tidak_hadir') {
                $query->whereIn('status', ['tidak_hadir', 'alpa']);
            } else {
                $query->where('status', $this->filterStatus);
            }
        }

        if ($this->filterTanggal) {
            $query->whereDate('tanggal', $this->filterTanggal);
        }

        $riwayat = $query->orderBy('tanggal', 'desc')->get();

        $lhkByTanggal = Lhk::withCount('pekerjaans')
            ->where('user_id', $userId)
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->get()
            ->keyBy(fn (Lhk $lhk) => Carbon::parse($lhk->tanggal)->format('Y-m-d'));

        $totalMenitBulanIni = (int) $allBulanIni->sum(fn (AbsensiHarian $a) => $a->hitungMenitKerjaEfektif());
        $targetBulananAsn = AbsensiHarian::hitungTargetBulananAsn((int) $this->bulan, (int) $this->tahun);
        $persenJamKerja = $targetBulananAsn['target_menit'] > 0
            ? min(100, (int) round(($totalMenitBulanIni / $targetBulananAsn['target_menit']) * 100))
            : 0;

        $cetakAll = ($this->cetakBulan === $this->bulan && $this->cetakTahun === $this->tahun)
            ? $allBulanIni
            : AbsensiHarian::where('user_id', $userId)
                ->where('is_simulasi', false)
                ->whereMonth('tanggal', $this->cetakBulan)
                ->whereYear('tanggal', $this->cetakTahun)
                ->orderBy('tanggal', 'asc')
                ->get();

        $cetakFiltered = $cetakAll->filter(function (AbsensiHarian $a): bool {
            $st = $a->status?->value ?? (string) $a->status;
            return match ($this->cetakStatus) {
                'hadir' => in_array($st, ['hadir', 'terlambat'], true),
                'terlambat' => $st === 'terlambat',
                'dinas_luar' => in_array($st, ['dinas_luar', 'dl'], true),
                'izin_sakit' => in_array($st, ['izin', 'sakit'], true),
                'tidak_hadir' => in_array($st, ['tidak_hadir', 'alpa'], true),
                default => true,
            };
        })->sortBy('tanggal')->values();

        $cetakRekap = [
            'total' => $cetakAll->count(),
            'hadir' => $cetakAll->filter(fn ($a) => in_array($a->status?->value ?? $a->status, ['hadir', 'terlambat'], true))->count(),
            'terlambat' => $cetakAll->filter(fn ($a) => ($a->status?->value ?? $a->status) === 'terlambat')->count(),
            'dinas_luar' => $cetakAll->filter(fn ($a) => in_array($a->status?->value ?? $a->status, ['dinas_luar', 'dl'], true))->count(),
            'izin_sakit' => $cetakAll->filter(fn ($a) => in_array($a->status?->value ?? $a->status, ['izin', 'sakit'], true))->count(),
            'alpa' => $cetakAll->filter(fn ($a) => in_array($a->status?->value ?? $a->status, ['tidak_hadir', 'alpa'], true))->count(),
        ];

        return view('livewire.mobile.riwayat', [
            'riwayat' => $riwayat,
            'lhkByTanggal' => $lhkByTanggal,
            'rekap' => $rekap,
            'cetakPreview' => [
                'periode' => Carbon::createFromDate($this->cetakTahun, $this->cetakBulan, 1)->translatedFormat('F Y'),
                'rekap' => $cetakRekap,
                'items' => $cetakFiltered,
                'user' => Auth::user(),
            ],
            'jamKerjaBulanan' => [
                'total_menit' => $totalMenitBulanIni,
                'total_format' => AbsensiHarian::formatDurasiJamMenit($totalMenitBulanIni),
                'target_format' => $targetBulananAsn['target_format'],
                'hari_kerja' => $targetBulananAsn['hari_kerja'],
                'persen' => $persenJamKerja,
                'target_harian_format' => AbsensiHarian::formatDurasiJamMenit(AbsensiHarian::TARGET_MENIT_HARIAN_ASN),
                'target_mingguan_format' => AbsensiHarian::formatDurasiJamMenit(AbsensiHarian::TARGET_MENIT_MINGGUAN_ASN),
            ],
            'bulanNama' => Carbon::createFromDate($this->tahun, $this->bulan, 1)->translatedFormat('F Y'),
        ]);
    }
}
