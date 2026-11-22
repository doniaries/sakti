<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiHarian;
use App\Models\HariLibur;
use App\Models\JadwalKerja;
use App\Models\Lhk;
use App\Models\PengaturanAplikasi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->loadMissing(['jabatan', 'jadwalKerja']);

        $today = Carbon::today();
        $bulan = (int) $request->get('bulan', $today->month);
        $tahun = (int) $request->get('tahun', $today->year);

        // Statistik absensi bulan ini
        $absensiQuery = AbsensiHarian::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        $totalHadir     = (clone $absensiQuery)->whereIn('status', ['hadir', 'terlambat'])->count();
        $totalTerlambat = (clone $absensiQuery)->where('status', 'terlambat')->count();
        $totalIzin      = (clone $absensiQuery)->where('status', 'izin')->count();
        $totalSakit     = (clone $absensiQuery)->where('status', 'sakit')->count();
        $totalLhk       = Lhk::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->count();

        // Absensi & hari libur hari ini
        $absensiHariIni = AbsensiHarian::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->with(['logMasuk', 'logPulang'])
            ->first();

        $hariLiburHariIni = HariLibur::where('tanggal', $today->format('Y-m-d'))
            ->where('is_aktif', true)
            ->first();

        $sudahMasuk = $absensiHariIni !== null && ($absensiHariIni->log_masuk_id !== null || !empty($absensiHariIni->jam_masuk));
        $sudahPulang = $absensiHariIni !== null && ($absensiHariIni->log_pulang_id !== null || !empty($absensiHariIni->jam_pulang));

        $jadwal = $user->getJadwalKerja() ?? JadwalKerja::getDefault();
        $bukaMasuk = $jadwal ? substr($jadwal->buka_absen_masuk, 0, 5) : '06:00';
        $tutupMasuk = $jadwal ? substr($jadwal->tutup_absen_masuk, 0, 5) : '09:00';
        $jamPulangResmi = $jadwal ? $jadwal->getJamPulangHari($today->format('Y-m-d')) : '16:00';
        $tutupPulang = $jadwal ? substr($jadwal->tutup_absen_pulang, 0, 5) : '23:59';

        $pengaturan = PengaturanAplikasi::getPengaturan();
        $modeSimulasi = (bool) ($pengaturan->mode_simulasi ?? false);
        $nowTime = ($modeSimulasi && !empty($pengaturan->simulasi_jam_aktif))
            ? substr($pengaturan->simulasi_jam_aktif, 0, 5)
            : Carbon::now()->format('H:i');

        $isAdmin = $user->hasAnyRole(['super_admin', 'admin']);
        $belumBukaMasuk = (!$isAdmin && $nowTime < $bukaMasuk);
        $lewatBatasMasuk = (!$isAdmin && !$sudahMasuk && $nowTime > $tutupMasuk);
        $belumJamPulang = (!$isAdmin && $nowTime < $jamPulangResmi);

        // Riwayat 7 hari terakhir (termasuk hari tanpa record)
        $absensi7Hari = AbsensiHarian::where('user_id', $user->id)
            ->whereBetween('tanggal', [$today->copy()->subDays(6)->format('Y-m-d'), $today->format('Y-m-d')])
            ->with(['logMasuk', 'logPulang'])
            ->get()
            ->keyBy(fn (AbsensiHarian $a) => Carbon::parse($a->tanggal)->format('Y-m-d'));

        $riwayat7Hari = [];
        for ($i = 0; $i <= 6; $i++) {
            $date = $today->copy()->subDays($i);
            $row = $absensi7Hari->get($date->format('Y-m-d'));
            $rawStatus = $row?->status;
            $statusVal = $rawStatus instanceof \BackedEnum ? (string) $rawStatus->value : (string) ($rawStatus ?? 'belum_ada');

            $riwayat7Hari[] = [
                'tanggal'    => $date->translatedFormat('d M Y'),
                'hari'       => $date->translatedFormat('l'),
                'status'     => $statusVal,
                'jam_masuk'  => $row?->logMasuk?->waktu_absensi?->format('H:i') ?? ($row?->jam_masuk ? substr($row->jam_masuk, 0, 5) : null),
                'jam_pulang' => $row?->logPulang?->waktu_absensi?->format('H:i') ?? ($row?->jam_pulang ? substr($row->jam_pulang, 0, 5) : null),
            ];
        }

        // Jam Kerja Efektif ASN
        $menitHariIni = $absensiHariIni ? $absensiHariIni->hitungMenitKerjaEfektif() : 0;
        $allBulanIni = AbsensiHarian::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();
        $totalMenitBulanIni = (int) $allBulanIni->sum(fn (AbsensiHarian $a) => $a->hitungMenitKerjaEfektif());
        $targetBulananAsn = AbsensiHarian::hitungTargetBulananAsn($bulan, $tahun);
        $persenBulanIni = $targetBulananAsn['target_menit'] > 0
            ? min(100, (int) round(($totalMenitBulanIni / $targetBulananAsn['target_menit']) * 100))
            : 0;

        return response()->json([
            'success' => true,
            'data'    => [
                'user'            => [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'nip'          => $user->nip,
                    'jabatan'      => $user->jabatan?->nama ?? 'Pegawai',
                    'status_kerja' => $user->status_kerja ?? 'wfo',
                    'avatar_url'   => $user->avatar_image_url,
                    'roles'        => $user->getRoleNames(),
                ],
                'bulan'           => $bulan,
                'tahun'           => $tahun,
                'hari_libur_nama' => $hariLiburHariIni?->nama,
                'statistik'       => [
                    'hadir'      => $totalHadir,
                    'terlambat'  => $totalTerlambat,
                    'izin'       => $totalIzin,
                    'sakit'      => $totalSakit,
                    'lhk_dibuat' => $totalLhk,
                ],
                'jam_kerja_asn'   => [
                    'harian_format'         => AbsensiHarian::formatDurasiJamMenit($menitHariIni),
                    'target_harian_format'  => AbsensiHarian::formatDurasiJamMenit(AbsensiHarian::TARGET_MENIT_HARIAN_ASN),
                    'persen_harian'         => min(100, (int) round(($menitHariIni / max(1, AbsensiHarian::TARGET_MENIT_HARIAN_ASN)) * 100)),
                    'bulanan_format'        => AbsensiHarian::formatDurasiJamMenit($totalMenitBulanIni),
                    'target_bulanan_format' => $targetBulananAsn['target_format'],
                    'persen_bulanan'        => $persenBulanIni,
                ],
                'hari_ini'        => [
                    'sudah_masuk'       => $sudahMasuk,
                    'sudah_pulang'      => $sudahPulang,
                    'jam_masuk'         => $absensiHariIni?->logMasuk?->waktu_absensi?->format('H:i:s') ?? $absensiHariIni?->jam_masuk,
                    'jam_pulang'        => $absensiHariIni?->logPulang?->waktu_absensi?->format('H:i:s') ?? $absensiHariIni?->jam_pulang,
                    'buka_masuk'        => $bukaMasuk,
                    'tutup_masuk'       => $tutupMasuk,
                    'jam_pulang_resmi'  => $jamPulangResmi,
                    'tutup_pulang'      => $tutupPulang,
                    'belum_buka_masuk'  => $belumBukaMasuk,
                    'lewat_batas_masuk' => $lewatBatasMasuk,
                    'belum_jam_pulang'  => $belumJamPulang,
                ],
                'riwayat_7_hari'  => $riwayat7Hari,
                'modul_aktif'     => PengaturanAplikasi::getModulAktifMap(),
                'app_info'        => [
                    'versi_aplikasi'   => $pengaturan->versi_aplikasi ?? 'v1.0.0',
                    'info_update'      => $pengaturan->info_update,
                    'pembuat_aplikasi' => $pengaturan->pembuat_aplikasi,
                    'kontak_bantuan'   => $pengaturan->kontak_bantuan,
                ],
            ],
        ]);
    }
}
