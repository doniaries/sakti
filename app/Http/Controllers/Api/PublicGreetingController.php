<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Models\PengaturanAplikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class PublicGreetingController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $today = Carbon::today();
        $todayLibur = HariLibur::getLibur($today);

        $upcomingLibur = null;
        if (! $todayLibur) {
            $upcomingLibur = HariLibur::query()
                ->where('is_aktif', true)
                ->whereDate('tanggal', '>', $today->toDateString())
                ->whereDate('tanggal', '<=', $today->copy()->addDays(7)->toDateString())
                ->orderBy('tanggal')
                ->first();
        }

        $pengaturan = PengaturanAplikasi::getPengaturan();
        $logoVersion = optional($pengaturan->updated_at)->timestamp ?? time();
        $logoUrl = filled($pengaturan->logo)
            ? asset('storage/' . $pengaturan->logo) . '?v=' . $logoVersion
            : asset('favicon.png') . '?v=' . $logoVersion;

        return response()->json([
            'app_name' => $pengaturan->nama_aplikasi ?? 'SAKTI',
            'sub_nama' => $pengaturan->sub_nama ?? 'Sistem Administrasi Kinerja Terintegrasi',
            'logo_url' => $logoUrl,
            'today_holiday' => $todayLibur ? [
                'tanggal' => $todayLibur->tanggal?->format('Y-m-d'),
                'nama' => $todayLibur->nama,
                'jenis' => $todayLibur->jenis,
                'keterangan' => $todayLibur->keterangan,
            ] : null,
            'upcoming_holiday' => $upcomingLibur ? [
                'tanggal' => $upcomingLibur->tanggal?->format('Y-m-d'),
                'nama' => $upcomingLibur->nama,
                'jenis' => $upcomingLibur->jenis,
                'keterangan' => $upcomingLibur->keterangan,
                'days_until' => $today->diffInDays($upcomingLibur->tanggal),
            ] : null,
        ]);
    }
}
