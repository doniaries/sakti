<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanIzin extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_izins';

    protected $fillable = [
        'user_id',
        'jenis_izin_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'alasan',
        'lampiran',
        'status',
        'disetujui_oleh',
        'catatan_atasan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'jumlah_hari' => 'integer',
    ];

    protected static function booted()
    {
        // Hitung jumlah hari otomatis jika belum diisi
        static::saving(function ($model) {
            if ($model->tanggal_mulai && $model->tanggal_selesai) {
                $start = Carbon::parse($model->tanggal_mulai);
                $end = Carbon::parse($model->tanggal_selesai);
                
                // Hitung hari kerja (Senin - Jumat)
                $count = 0;
                $period = CarbonPeriod::create($start, $end);
                foreach ($period as $date) {
                    if ($date->isWeekday()) {
                        $count++;
                    }
                }
                $model->jumlah_hari = max(1, $count);
            }
        });

        // Sinkronisasi otomatis ke AbsensiHarian saat status disetujui
        static::saved(function ($model) {
            if ($model->status === 'disetujui') {
                $model->sinkronkanKeAbsensiHarian();
            }
        });
    }

    /**
     * Generate / update entri absensi_harian otomatis
     */
    public function sinkronkanKeAbsensiHarian(): void
    {
        $start = Carbon::parse($this->tanggal_mulai);
        $end = Carbon::parse($this->tanggal_selesai);
        $jenisIzin = $this->jenisIzin;

        $period = CarbonPeriod::create($start, $end);
        foreach ($period as $date) {
            // Hanya buat untuk hari kerja (Senin - Jumat)
            if (!$date->isWeekday()) {
                continue;
            }

            $tanggalStr = $date->format('Y-m-d');
            $statusAbsensi = match ($jenisIzin?->kategori) {
                'cuti' => 'cuti',
                'sakit' => 'sakit',
                'izin' => 'izin',
                'wfh' => 'hadir',
                default => 'izin',
            };

            $jenisKehadiran = ($jenisIzin?->kategori === 'wfh') ? 'wfh' : 'wfo';

            AbsensiHarian::updateOrCreate(
                [
                    'user_id' => $this->user_id,
                    'tanggal' => $tanggalStr,
                ],
                [
                    'status' => $statusAbsensi,
                    'jenis_kehadiran' => $jenisKehadiran,
                    'pengajuan_izin_id' => $this->id,
                    'keterangan' => ($jenisIzin?->nama ?? 'Izin') . ': ' . $this->alasan,
                ]
            );
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jenisIzin(): BelongsTo
    {
        return $this->belongsTo(JenisIzin::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function absensiHarians(): HasMany
    {
        return $this->hasMany(AbsensiHarian::class, 'pengajuan_izin_id');
    }
}
