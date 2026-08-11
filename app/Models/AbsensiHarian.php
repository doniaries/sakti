<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AbsensiHarian extends Model
{
    protected $table = 'absensi_harian';

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'log_masuk_id',
        'log_pulang_id',
        'status',
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
}
