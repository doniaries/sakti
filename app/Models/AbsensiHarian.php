<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AbsensiHarian extends Model
{
    use SoftDeletes;
    public const TARGET_MENIT_HARIAN_ASN = 450; // 7 jam 30 menit per hari kerja
    public const TARGET_MENIT_MINGGUAN_ASN = 2250; // 37 jam 30 menit per minggu (5 hari kerja)

    protected $table = 'absensi_harian';

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'log_masuk_id',
        'log_pulang_id',
        'status',
        'jenis_kehadiran',
        'jadwal_kerja_id',
        'pengajuan_izin_id',
        'keterangan',
        'is_absen_mesin',
        'is_absen_mobile',
        'status_apel',
        'latitude_masuk',
        'longitude_masuk',
        'foto_masuk',
        'latitude_pulang',
        'longitude_pulang',
        'foto_pulang',
        'bukti',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_absen_mesin' => 'boolean',
        'is_absen_mobile' => 'boolean',
        'status' => \App\Enums\StatusKehadiran::class,
        'jenis_kehadiran' => \App\Enums\JenisKehadiran::class,
        'status_apel' => \App\Enums\StatusApel::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            $files = ['foto_masuk', 'foto_pulang', 'bukti'];
            foreach ($files as $file) {
                if ($model->isDirty($file) && ($oldFile = $model->getOriginal($file))) {
                    Storage::disk('public')->delete($oldFile);
                }
            }
        });

        static::deleting(function ($model) {
            $files = ['foto_masuk', 'foto_pulang', 'bukti'];
            foreach ($files as $file) {
                if ($model->$file) {
                    Storage::disk('public')->delete($model->$file);
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logMasuk(): BelongsTo
    {
        return $this->belongsTo(LogAbsensi::class, 'log_masuk_id');
    }

    public function logPulang(): BelongsTo
    {
        return $this->belongsTo(LogAbsensi::class, 'log_pulang_id');
    }

    public function jadwalKerja(): BelongsTo
    {
        return $this->belongsTo(JadwalKerja::class);
    }

    public function pengajuanIzin(): BelongsTo
    {
        return $this->belongsTo(PengajuanIzin::class);
    }

    /**
     * Hitung durasi kerja efektif harian dalam satuan menit (tidak termasuk jam istirahat)
     * sesuai ketentuan ASN: 37 jam 30 menit/minggu atau rata-rata 7 jam 30 menit/hari.
     */
    public function hitungMenitKerjaEfektif(): int
    {
        $status = $this->status?->value ?? $this->status;

        if (! empty($this->jam_masuk) && ! empty($this->jam_pulang)) {
            $tgl = $this->tanggal ? Carbon::parse($this->tanggal)->format('Y-m-d') : Carbon::today()->format('Y-m-d');
            $masuk = Carbon::parse($tgl . ' ' . $this->jam_masuk);
            $pulang = Carbon::parse($tgl . ' ' . $this->jam_pulang);

            if ($pulang->lessThanOrEqualTo($masuk)) {
                return 0;
            }

            $totalMenit = (int) $masuk->diffInMinutes($pulang);

            // Jam istirahat: Senin-Kamis 12:00-13:00 (60m), Jumat 11:30-13:00 (90m)
            $isJumat = Carbon::parse($tgl)->dayOfWeekIso === 5;
            $istirahatMulai = Carbon::parse($tgl . ' ' . ($isJumat ? '11:30:00' : '12:00:00'));
            $istirahatSelesai = Carbon::parse($tgl . ' 13:00:00');

            $overlapStart = $masuk->greaterThan($istirahatMulai) ? $masuk : $istirahatMulai;
            $overlapEnd = $pulang->lessThan($istirahatSelesai) ? $pulang : $istirahatSelesai;
            $potonganIstirahat = $overlapEnd->greaterThan($overlapStart)
                ? (int) $overlapStart->diffInMinutes($overlapEnd)
                : 0;

            return max(0, $totalMenit - $potonganIstirahat);
        }

        if ($status === 'dinas_luar') {
            return self::TARGET_MENIT_HARIAN_ASN;
        }

        return 0;
    }

    public static function formatDurasiJamMenit(int $menit): string
    {
        $menit = max(0, $menit);
        $jam = intdiv($menit, 60);
        $sisaMenit = $menit % 60;

        return sprintf('%dj %02dm', $jam, $sisaMenit);
    }

    /**
     * Hitung jumlah hari kerja efektif (Senin-Jumat non-libur) dan target menit ASN dalam 1 bulan.
     *
     * @return array{hari_kerja: int, target_menit: int, target_format: string}
     */
    public static function hitungTargetBulananAsn(int $bulan, int $tahun): array
    {
        $start = Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();

        $liburDates = HariLibur::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('is_aktif', true)
            ->pluck('tanggal')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->flip();

        $hariKerja = 0;
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            if (! $day->isWeekend() && ! $liburDates->has($day->format('Y-m-d'))) {
                $hariKerja++;
            }
        }

        $targetMenit = $hariKerja * self::TARGET_MENIT_HARIAN_ASN;

        return [
            'hari_kerja' => $hariKerja,
            'target_menit' => $targetMenit,
            'target_format' => self::formatDurasiJamMenit($targetMenit),
        ];
    }

    /**
     * Upsert aman terhadap SoftDeletes agar unique key (user_id, tanggal) tidak bentrok dengan baris trashed.
     */
    public static function upsertSafe(array $attributes, array $values = []): self
    {
        $record = static::withTrashed()->firstOrNew($attributes);

        if ($record->trashed()) {
            $record->deleted_at = null;
        }

        $record->fill($values);
        $record->save();

        return $record;
    }
}
