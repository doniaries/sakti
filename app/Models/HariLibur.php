<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 */
class HariLibur extends Model
{
    use HasFactory;

    protected $table = 'hari_liburs';

    protected $fillable = [
        'tanggal',
        'nama',
        'jenis',
        'sumber',
        'keterangan',
        'is_aktif',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_aktif' => 'boolean',
    ];

    /**
     * Cek apakah suatu tanggal adalah hari libur (Sabtu, Minggu, atau terdaftar di hari_liburs)
     */
    public static function isHariLibur(Carbon|string $tanggal): bool
    {
        $date = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        // Jika akhir pekan (Sabtu / Minggu)
        if ($date->isWeekend()) {
            return true;
        }

        return static::whereDate('tanggal', $date->toDateString())
            ->where('is_aktif', true)
            ->exists();
    }

    /**
     * Dapatkan data hari libur jika ada
     */
    public static function getLibur(Carbon|string $tanggal): ?self
    {
        $date = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        return static::whereDate('tanggal', $date->toDateString())
            ->where('is_aktif', true)
            ->first();
    }

    /**
     * Dapatkan informasi status non-hari-kerja (Sabtu, Minggu, Tanggal Merah, atau Cuti Bersama)
     *
     * @return array{is_libur: bool, jenis: string|null, label: string|null, keterangan: string|null, record: self|null}
     */
    public static function getInfoHariLibur(Carbon|string $tanggal): array
    {
        $date = $tanggal instanceof Carbon ? $tanggal->copy() : Carbon::parse($tanggal);
        $record = static::getLibur($date);

        if ($record) {
            $jenisLabel = $record->jenis === 'cuti_bersama' ? 'Cuti Bersama' : 'Hari Libur Nasional';

            return [
                'is_libur' => true,
                'jenis' => $record->jenis,
                'label' => "{$jenisLabel}: {$record->nama}",
                'keterangan' => "Hari ini adalah {$jenisLabel} ({$record->nama}). Bukan jadwal kerja.",
                'record' => $record,
            ];
        }

        if ($date->isWeekend()) {
            $hariNama = $date->copy()->locale('id')->translatedFormat('l');

            return [
                'is_libur' => true,
                'jenis' => 'akhir_pekan',
                'label' => "Bukan Jadwal Kerja ({$hariNama})",
                'keterangan' => "Hari {$hariNama} merupakan akhir pekan (bukan jadwal kerja). Presensi hanya dibuka pada hari kerja Senin - Jumat.",
                'record' => null,
            ];
        }

        return [
            'is_libur' => false,
            'jenis' => null,
            'label' => null,
            'keterangan' => null,
            'record' => null,
        ];
    }

    /**
     * Sinkronkan data hari libur nasional & cuti bersama otomatis dari API SKB
     */
    public static function syncFromApi(int $year): int
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->timeout(10)
                ->get("https://api-hari-libur.vercel.app/api?year={$year}");
            if (!$response->successful()) {
                return 0;
            }

            $json = $response->json();
            $data = $json['data'] ?? [];
            if (!is_array($data) || empty($data)) {
                return 0;
            }

            $count = 0;
            foreach ($data as $item) {
                if (empty($item['date']) || empty($item['description'])) {
                    continue;
                }

                $tanggal = $item['date'];
                $nama = trim($item['description']);
                $isCuti = str_contains(strtolower($nama), 'cuti bersama');
                $jenis = $isCuti ? 'cuti_bersama' : 'nasional';

                static::updateOrCreate(
                    ['tanggal' => $tanggal],
                    [
                        'nama' => $nama,
                        'jenis' => $jenis,
                        'sumber' => 'api',
                        'keterangan' => 'Disinkronkan otomatis dari API Resmi SKB Hari Libur Nasional & Cuti Bersama.',
                        'is_aktif' => true,
                    ]
                );
                $count++;
            }

            return $count;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal sinkronisasi hari libur dari API: ' . $e->getMessage());
            return 0;
        }
    }
}
