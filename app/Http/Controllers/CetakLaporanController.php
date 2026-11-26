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
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user && $request->hasValidSignature() && $request->filled('uid')) {
            $user = User::findOrFail((int) $request->query('uid'));
        }
        abort_unless($user, 401);

        // Admin / Superadmin bisa cetak punya bawahan/pegawai dengan menyertakan ?user_id=
        if ($request->has('user_id') && ($user->hasRole(['super_admin', 'admin']) || $user->id == $request->query('user_id'))) {
            $user = User::find($request->query('user_id')) ?? $user;
        }

        $bulan = $request->query('bulan', date('m'));
        $tahun = $request->query('tahun', date('Y'));
        $status = $request->query('status', 'semua');

        $query = AbsensiHarian::where('user_id', $user->id)
            ->where('is_simulasi', false)
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
            ->where('is_simulasi', false)
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

    public function verifikasiIzin(Request $request, ?string $kode = null)
    {
        $input = trim((string) ($request->query('kode') ?? $request->query('id') ?? $kode ?? ''));
        $izin = null;

        if (filled($input)) {
            $decoded = base64_decode(strtr($input, '-_', '+/'), true);
            $idDecoded = ($decoded !== false && ctype_digit($decoded)) ? (int) $decoded : null;

            $izin = \App\Models\PengajuanIzin::query()
                ->with(['user.jabatan', 'user.bidang', 'jenisIzin', 'approver.jabatan'])
                ->when($idDecoded !== null, fn ($q) => $q->where('id', $idDecoded))
                ->when($idDecoded === null && ctype_digit($input), fn ($q) => $q->where('id', (int) $input))
                ->first();
        }

        $isValid = $izin !== null
            && $izin->status === 'disetujui'
            && filled($izin->hash_dokumen)
            && hash_equals($izin->hash_dokumen, $izin->hitungHashDokumen());

        return view('dokumen.verifikasi-izin', [
            'izin' => $izin,
            'instansi' => \App\Models\Instansi::first(),
            'kode' => $input,
            'isValid' => $isValid,
        ]);
    }

    public function cetakIzinCuti(Request $request, \App\Models\PengajuanIzin $pengajuanIzin)
    {
        $pengajuanIzin->load(['user.jabatan', 'user.bidang', 'user.atasan', 'jenisIzin', 'approver']);
        $instansi = \App\Models\Instansi::first();
        $pengaturan = PengaturanAplikasi::getPengaturan();

        $logoPath = null;
        if (! empty($instansi?->logo) && file_exists(public_path('storage/' . $instansi->logo))) {
            $logoPath = $request->boolean('preview')
                ? asset('storage/' . $instansi->logo)
                : public_path('storage/' . $instansi->logo);
        } elseif (file_exists(public_path('images/logo-kabupaten.png'))) {
            $logoPath = $request->boolean('preview')
                ? asset('images/logo-kabupaten.png')
                : public_path('images/logo-kabupaten.png');
        }

        $sisaCutiTahunan = $pengajuanIzin->user?->sisa_cuti ?? 12;

        $qrBase64 = null;
        $trackingUrl = null;
        if ($pengajuanIzin->status === 'disetujui') {
            $qrBase64 = $pengajuanIzin->getQrTrackingBase64();
            $trackingUrl = $pengajuanIzin->getTrackingUrl();
        }

        $viewData = [
            'izin' => $pengajuanIzin,
            'instansi' => $instansi,
            'pengaturan' => $pengaturan,
            'logoPath' => $logoPath,
            'sisaCutiTahunan' => $sisaCutiTahunan,
            'qrBase64' => $qrBase64,
            'trackingUrl' => $trackingUrl,
        ];

        $viewName = ($pengajuanIzin->jenisIzin?->kategori === 'cuti') ? 'pdf.surat-cuti' : 'pdf.surat-izin';

        if ($request->boolean('preview')) {
            return view($viewName, $viewData);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, $viewData)->setPaper('a4', 'portrait');

        $prefix = ($pengajuanIzin->jenisIzin?->kategori === 'cuti') ? 'Formulir-Cuti-' : 'Surat-Izin-';
        $namaFile = $prefix . str($pengajuanIzin->user?->name ?? 'Pegawai')->slug() . '.pdf';

        return $request->boolean('download')
            ? $pdf->download($namaFile)
            : $pdf->stream($namaFile);
    }

    public function cetakPegawaiBelumLengkap()
    {
        /** @var \App\Models\User|null $authUser */
        $authUser = Auth::user();
        abort_unless($authUser && $authUser->hasAnyRole(['super_admin', 'superadmin', 'admin', 'admin_kepegawaian', 'sekretaris', 'kepala_dinas']), 403);

        $pengaturan = \App\Models\PengaturanAplikasi::getPengaturan();
        $pegawais = \App\Models\User::query()
            ->with(['bidang:id,nama', 'jabatan:id,nama'])
            ->where('aktif', true)
            ->where(function ($q) {
                $q->whereNull('nip')->orWhere('nip', '')
                    ->orWhereNull('nik')->orWhere('nik', '')
                    ->orWhereNull('no_hp')->orWhere('no_hp', '')
                    ->orWhereNull('bidang_id')
                    ->orWhereNull('jabatan_id')
                    ->orWhereNull('status_kepegawaian');
            })
            ->orderBy('name')
            ->get();

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.pegawai-belum-lengkap', [
            'pegawais' => $pegawais,
            'pengaturan' => $pengaturan,
            'dicetakOleh' => $authUser,
        ])->setPaper('a4', 'portrait')->stream('laporan-pegawai-belum-lengkap.pdf');
    }
}
