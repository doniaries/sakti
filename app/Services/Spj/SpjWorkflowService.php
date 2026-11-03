<?php

namespace App\Services\Spj;

use App\Models\PengajuanSpj;
use App\Models\RiwayatStatusSpj;
use App\Models\VerifikasiSpj;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SpjWorkflowService
{
    /**
     * Transisi status SPJ dengan database transaction & pencatatan audit log
     */
    public function transition(
        PengajuanSpj $spj,
        string $nextStatus,
        ?string $catatan = null,
        ?string $peran = null,
        ?array $checklist = null
    ): bool {
        return DB::transaction(function () use ($spj, $nextStatus, $catatan, $peran, $checklist) {
            $user = Auth::user();
            $prevStatus = $spj->status;

            // Update status dan snapshot pejabat yang mengeksekusi
            $updateData = ['status' => $nextStatus];
            
            if ($catatan) {
                $updateData['catatan_revisi'] = $catatan;
            }

            if ($user) {
                if (in_array($nextStatus, ['menunggu_realisasi', 'menunggu_verifikasi_ppk', 'revisi_pptk'], true)) {
                    $updateData['pptk_user_id'] = $user->id;
                } elseif ($nextStatus === 'menunggu_persetujuan_pa' || $nextStatus === 'revisi_ppk') {
                    $updateData['ppk_user_id'] = $user->id;
                } elseif ($nextStatus === 'menunggu_verifikasi_bendahara' || $nextStatus === 'revisi_pa') {
                    $updateData['pa_user_id'] = $user->id;
                } elseif ($nextStatus === 'dicairkan' || $nextStatus === 'revisi_bendahara') {
                    $updateData['bendahara_user_id'] = $user->id;
                    if ($nextStatus === 'dicairkan') {
                        $updateData['tanggal_dicairkan'] = now()->toDateString();
                    }
                }
            }

            $spj->update($updateData);

            // Simpan audit trail
            RiwayatStatusSpj::create([
                'pengajuan_spj_id' => $spj->id,
                'status_sebelumnya' => $prevStatus,
                'status_berikutnya' => $nextStatus,
                'user_id' => $user?->id ?? $spj->user_id,
                'peran_saat_tindakan' => $peran,
                'catatan' => $catatan,
                'created_at' => now(),
            ]);

            // Simpan checklist verifikasi jika disertakan
            if ($checklist && $peran && $user) {
                VerifikasiSpj::create([
                    'pengajuan_spj_id' => $spj->id,
                    'tahap' => strtolower($peran),
                    'user_id' => $user->id,
                    'checklist_data' => $checklist,
                    'catatan' => $catatan,
                    'is_approved' => !str_contains($nextStatus, 'revisi') && $nextStatus !== 'ditolak',
                ]);
            }

            return true;
        });
    }

    /**
     * Konversi angka nominal rupiah ke teks terbilang bahasa Indonesia
     */
    public static function terbilang(float|int $nilai): string
    {
        $nilai = abs($nilai);
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $temp = '';

        if ($nilai < 12) {
            $temp = ' ' . $huruf[(int) $nilai];
        } elseif ($nilai < 20) {
            $temp = self::terbilang($nilai - 10) . ' Belas';
        } elseif ($nilai < 100) {
            $temp = self::terbilang((int) ($nilai / 10)) . ' Puluh' . self::terbilang($nilai % 10);
        } elseif ($nilai < 200) {
            $temp = ' Seratus' . self::terbilang($nilai - 100);
        } elseif ($nilai < 1000) {
            $temp = self::terbilang((int) ($nilai / 100)) . ' Ratus' . self::terbilang($nilai % 100);
        } elseif ($nilai < 2000) {
            $temp = ' Seribu' . self::terbilang($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $temp = self::terbilang((int) ($nilai / 1000)) . ' Ribu' . self::terbilang($nilai % 1000);
        } elseif ($nilai < 1000000000) {
            $temp = self::terbilang((int) ($nilai / 1000000)) . ' Juta' . self::terbilang($nilai % 1000000);
        } elseif ($nilai < 1000000000000) {
            $temp = self::terbilang((int) ($nilai / 1000000000)) . ' Milyar' . self::terbilang(fmod($nilai, 1000000000));
        } elseif ($nilai < 1000000000000000) {
            $temp = self::terbilang((int) ($nilai / 1000000000000)) . ' Triliun' . self::terbilang(fmod($nilai, 1000000000000));
        }

        return trim($temp) . ' Rupiah';
    }
}

