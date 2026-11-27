<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RencanaAksi extends Model
{
    protected $fillable = [
        'jabatan_id',
        'bidang_id',
        'level',
        'tahun',
        'sasaran_opd',
        'sasaran_program',
        'sasaran_kegiatan',
        'sasaran_sub_kegiatan',
        'sasaran_teknis',
        'indikator',
        'rencana_aksi',
        'jenis',
        'satuan',
        'target_jan',
        'target_feb',
        'target_mar',
        'target_apr',
        'target_mei',
        'target_jun',
        'target_jul',
        'target_agu',
        'target_sep',
        'target_okt',
        'target_nov',
        'target_des',
    ];

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class);
    }

    /**
     * Scope Rencana Aksi sesuai jabatan/level, bidang, dan status Tim Kreatif pegawai.
     */
    public static function untukUser(?User $user): Builder
    {
        $query = self::query()
            ->select(['id', 'bidang_id', 'jabatan_id', 'sasaran_sub_kegiatan', 'rencana_aksi', 'level'])
            ->orderBy('sasaran_sub_kegiatan')
            ->orderBy('rencana_aksi');

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $jabatanNama = mb_strtolower((string) ($user->jabatan?->nama ?? ''));
        $userLevel = match (true) {
            $user->hasAnyRole(['kabid', 'kepala_bidang', 'pptk', 'sekretaris', 'pa'])
                || str_contains($jabatanNama, 'kepala bidang')
                || str_contains($jabatanNama, 'kabid')
                || str_contains($jabatanNama, 'sekretaris')
                || str_contains($jabatanNama, 'kepala dinas') => 'kabid',
            $user->hasRole('jf')
                || str_contains($jabatanNama, 'fungsional')
                || str_contains($jabatanNama, 'analis')
                || str_contains($jabatanNama, 'adya')
                || str_contains($jabatanNama, 'muda')
                || str_contains($jabatanNama, 'pertama') => 'jf',
            default => 'staf',
        };

        return $query->where(function (Builder $q) use ($user, $userLevel): void {
            $q->where(function (Builder $bidangQ) use ($user, $userLevel): void {
                $bidangQ->where('sasaran_sub_kegiatan', '!=', 'Tugas Tambahan')
                    ->where(function (Builder $b) use ($user): void {
                        $b->whereNull('bidang_id');
                        if ($user->bidang_id) {
                            $b->orWhere('bidang_id', $user->bidang_id);
                        }
                    })
                    ->where(function (Builder $lvl) use ($user, $userLevel): void {
                        $lvl->where('level', $userLevel);
                        if ($user->jabatan_id) {
                            $lvl->orWhere('jabatan_id', $user->jabatan_id);
                        }
                    });
            });

            if ($user->jabatan_tim_kreatif) {
                $timKreatifRaIds = Aktivitas::query()
                    ->where('user_id', $user->id)
                    ->whereNotNull('rencana_aksi_id')
                    ->pluck('rencana_aksi_id')
                    ->all();

                $q->orWhere(function (Builder $tkQ) use ($userLevel, $timKreatifRaIds): void {
                    $tkQ->where('sasaran_sub_kegiatan', 'Tugas Tambahan');
                    if ($timKreatifRaIds !== []) {
                        $tkQ->whereIn('id', $timKreatifRaIds);
                    } else {
                        $tkQ->where('level', $userLevel);
                    }
                });
            }
        });
    }
}
