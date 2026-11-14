<?php

namespace App\Http\Controllers;

use App\Models\AbsensiHarian;
use App\Models\PengaturanAplikasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CetakLaporanController extends Controller
{
    public function cetakRiwayat(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Admin / Superadmin bisa cetak punya bawahan/pegawai dengan menyertakan ?user_id=
        if ($request->has('user_id') && ($user->hasRole(['super_admin', 'admin']) || $user->id == $request->query('user_id'))) {
            $user = User::find($request->query('user_id')) ?? $user;
        }

        $bulan = $request->query('bulan', date('m'));
        $tahun = $request->query('tahun', date('Y'));
        $status = $request->query('status', 'semua');

        $query = AbsensiHarian::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        if ($status && $status !== 'semua') {
            if ($status === 'hadir') {
                $query->whereIn('status', ['hadir', 'terlambat']);
            } elseif ($status === 'izin_sakit') {
                $query->whereIn('status', ['izin', 'sakit']);
            } elseif ($status === 'tidak_hadir') {
                $query->whereIn('status', ['tidak_hadir', 'alpa']);
            } else {
                $query->where('status', $status);
            }
        }

        $riwayat = $query->orderBy('tanggal', 'asc')->get();

        $allBulanIni = AbsensiHarian::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $rekap = [
            'total' => $allBulanIni->count(),
            'hadir' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['hadir', 'terlambat']))->count(),
            'terlambat' => $allBulanIni->filter(fn($a) => ($a->status?->value ?? $a->status) === 'terlambat')->count(),
            'dinas_luar' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['dinas_luar', 'dl']))->count(),
            'izin_sakit' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['izin', 'sakit']))->count(),
            'alpa' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['tidak_hadir', 'alpa']))->count(),
        ];

        $instansi = \App\Models\Instansi::first();
        $pengaturan = PengaturanAplikasi::getPengaturan();
        $periode = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');

        return view('cetak.riwayat-pdf', compact('user', 'riwayat', 'rekap', 'pengaturan', 'instansi', 'periode', 'status'));
    }

    public function cetakIzinCuti(\App\Models\PengajuanIzin $pengajuanIzin)
    {
        $pengajuanIzin->load(['user.jabatan', 'user.bidang', 'user.atasan', 'jenisIzin', 'approver']);
        $instansi = \App\Models\Instansi::with('kadis')->first();
        $pengaturan = PengaturanAplikasi::getPengaturan();

        $logoPath = null;
        if (! empty($instansi?->logo) && file_exists(public_path('storage/' . $instansi->logo))) {
            $logoPath = public_path('storage/' . $instansi->logo);
        } elseif (file_exists(public_path('images/logo-kabupaten.png'))) {
            $logoPath = public_path('images/logo-kabupaten.png');
        }

        $sisaCutiTahunan = $pengajuanIzin->user?->sisa_cuti ?? 12;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.surat-cuti', [
            'izin' => $pengajuanIzin,
            'instansi' => $instansi,
            'pengaturan' => $pengaturan,
            'logoPath' => $logoPath,
            'sisaCutiTahunan' => $sisaCutiTahunan,
        ])->setPaper('a4', 'portrait');

        $namaFile = 'Formulir-Cuti-' . str($pengajuanIzin->user?->name ?? 'Pegawai')->slug() . '.pdf';

        return $pdf->stream($namaFile);
    }
}
