<?php

namespace App\Livewire\Mobile\Lhk;

use App\Models\AbsensiHarian;
use App\Models\Lhk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('LHK - Absensi LHK Mobile')]
class Index extends Component
{
    public function render()
    {
        $userId = Auth::id();

        $lhksByDate = Lhk::with('pekerjaans')
            ->where('user_id', $userId)
            ->orderBy('tanggal', 'desc')
            ->get()
            ->keyBy(fn (Lhk $lhk) => Carbon::parse($lhk->tanggal)->format('Y-m-d'));

        $absensiList = AbsensiHarian::where('user_id', $userId)
            ->orderBy('tanggal', 'desc')
            ->get()
            ->filter(function (AbsensiHarian $absen): bool {
                $status = $absen->status?->value ?? $absen->status;
                $sudahMasuk = ! empty($absen->log_masuk_id) || ! empty($absen->jam_masuk);

                return $sudahMasuk || in_array($status, ['hadir', 'terlambat', 'dinas_luar'], true);
            })
            ->keyBy(fn (AbsensiHarian $absen) => Carbon::parse($absen->tanggal)->format('Y-m-d'));

        $allDates = $absensiList->keys()
            ->merge($lhksByDate->keys())
            ->unique()
            ->sortDesc()
            ->values();

        $daftarHadirLhk = $allDates->map(function (string $tgl) use ($absensiList, $lhksByDate): array {
            /** @var AbsensiHarian|null $absen */
            $absen = $absensiList->get($tgl);
            /** @var Lhk|null $lhk */
            $lhk = $lhksByDate->get($tgl);

            $rawStatus = $absen ? ($absen->status?->value ?? $absen->status) : null;
            $statusLabel = match ($rawStatus) {
                'hadir' => 'Hadir',
                'terlambat' => 'Terlambat',
                'dinas_luar' => 'Dinas Luar',
                'izin' => 'Izin',
                'sakit' => 'Sakit',
                default => $lhk?->status_kehadiran ?? 'Hadir',
            };

            $jamMasuk = $absen?->jam_masuk ?? $lhk?->jam_masuk;
            $jamPulang = $absen?->jam_pulang ?? $lhk?->jam_pulang;
            $jumlahKegiatan = $lhk ? $lhk->pekerjaans->count() : 0;

            return [
                'tanggal' => $tgl,
                'status' => $statusLabel,
                'jam_masuk' => $jamMasuk ? substr((string) $jamMasuk, 0, 5) : null,
                'jam_pulang' => $jamPulang ? substr((string) $jamPulang, 0, 5) : null,
                'jumlah_kegiatan' => $jumlahKegiatan,
                'sudah_ada_lhk' => $lhk !== null && $jumlahKegiatan > 0,
            ];
        });

        return view('livewire.mobile.lhk.index', [
            'daftarHadirLhk' => $daftarHadirLhk,
        ]);
    }
}
