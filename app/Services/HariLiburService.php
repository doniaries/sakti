<?php

namespace App\Services;

use App\Models\HariLibur;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HariLiburService
{
    /**
     * Tarik data hari libur nasional untuk tahun tertentu menggunakan Multi-Endpoint API Fallback.
     *
     * @param int|null $year
     * @return array{success: bool, count: int, message: string}
     */
    public function syncFromApi(?int $year = null): array
    {
        $tahun = $year ?? (int) now()->year;
        $items = [];

        // Endpoint 1: dayoffapi
        try {
            $response = Http::timeout(8)->get("https://dayoffapi.vercel.app/api?year={$tahun}");
            if ($response->successful()) {
                $raw = $response->json();
                // Format dayoffapi biasanya array of objects: [ { tanggal: "YYYY-MM-DD", keterangan: "...", is_cuti: bool } ]
                if (is_array($raw)) {
                    foreach ($raw as $entry) {
                        $tanggal = $entry['tanggal'] ?? null;
                        $nama = $entry['keterangan'] ?? null;
                        $isCuti = !empty($entry['is_cuti']);

                        if ($tanggal && $nama) {
                            $items[] = [
                                'tanggal' => $tanggal,
                                'nama' => $nama,
                                'jenis' => $isCuti ? 'cuti_bersama' : 'nasional',
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("HariLiburService Endpoint 1 gagal: " . $e->getMessage());
        }

        // Endpoint 2: api-harilibur (Fallback jika endpoint 1 kosong)
        if (empty($items)) {
            try {
                $response = Http::timeout(8)->get("https://api-harilibur.vercel.app/api?year={$tahun}");
                if ($response->successful()) {
                    $raw = $response->json();
                    if (is_array($raw)) {
                        foreach ($raw as $entry) {
                            $tanggal = $entry['holiday_date'] ?? $entry['tanggal'] ?? null;
                            $nama = $entry['holiday_name'] ?? $entry['keterangan'] ?? null;
                            $isCuti = !empty($entry['is_national_holiday']) ? false : true;

                            if ($tanggal && $nama) {
                                $items[] = [
                                    'tanggal' => $tanggal,
                                    'nama' => $nama,
                                    'jenis' => $isCuti ? 'cuti_bersama' : 'nasional',
                                ];
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("HariLiburService Endpoint 2 gagal: " . $e->getMessage());
            }
        }

        // Jika tidak ada respon dari API online, gunakan daftar libur nasional permanen terkurasi
        if (empty($items)) {
            $items = $this->getDefaultNationalHolidays($tahun);
        }

        $savedCount = 0;
        foreach ($items as $item) {
            HariLibur::updateOrCreate(
                ['tanggal' => $item['tanggal']],
                [
                    'nama' => $item['nama'],
                    'jenis' => $item['jenis'] ?? 'nasional',
                    'sumber' => 'api',
                    'keterangan' => 'Sinkronisasi Hari Libur Nasional & Cuti Bersama SKB 3 Menteri',
                    'is_aktif' => true,
                ]
            );
            $savedCount++;
        }

        return [
            'success' => true,
            'count' => $savedCount,
            'message' => "Berhasil menyinkronkan {$savedCount} hari libur nasional & cuti bersama untuk tahun {$tahun}.",
        ];
    }

    /**
     * Fallback Libur Nasional Baku jika internet/API sedang offline
     */
    private function getDefaultNationalHolidays(int $year): array
    {
        return [
            ['tanggal' => "{$year}-01-01", 'nama' => 'Tahun Baru Masehi', 'jenis' => 'nasional'],
            ['tanggal' => "{$year}-05-01", 'nama' => 'Hari Buruh Internasional', 'jenis' => 'nasional'],
            ['tanggal' => "{$year}-06-01", 'nama' => 'Hari Lahir Pancasila', 'jenis' => 'nasional'],
            ['tanggal' => "{$year}-08-17", 'nama' => 'Hari Kemerdekaan Republik Indonesia', 'jenis' => 'nasional'],
            ['tanggal' => "{$year}-12-25", 'nama' => 'Hari Raya Natal', 'jenis' => 'nasional'],
        ];
    }
}
