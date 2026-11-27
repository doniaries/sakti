<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aktivitas extends Model
{
    protected $fillable = ['user_id', 'jabatan_id', 'bidang_id', 'rencana_aksi_id', 'kode', 'nama_aktivitas', 'menit', 'status', 'approved_by', 'approved_at'];

    public function rencanaAksi(): BelongsTo
    {
        return $this->belongsTo(RencanaAksi::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class);
    }

    /**
     * Filter aktivitas milik pegawai tertentu (prioritas utama),
     * atau aktivitas master umum (user_id null) yang sesuai jabatan / global.
     */
    public function scopeUntukUser(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->orderBy('nama_aktivitas');
        }

        return $query
            ->where(function (Builder $q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere(function (Builder $q2) use ($user) {
                        $q2->whereNull('user_id')
                            ->where(function (Builder $q3) use ($user) {
                                $q3->whereNull('jabatan_id');
                                if ($user->jabatan_id) {
                                    $q3->orWhere('jabatan_id', $user->jabatan_id);
                                }
                            });
                    });
            })
            ->orderByRaw("CASE WHEN LOWER(nama_aktivitas) LIKE '%apel pagi%' THEN 0 ELSE 1 END")
            ->orderByRaw('COALESCE(menit, 0) ASC')
            ->orderByRaw('CASE WHEN user_id = ? THEN 0 ELSE 1 END', [$user->id])
            ->orderBy('nama_aktivitas');
    }

    /**
     * Parse teks tempelan (copy-paste dari tabel HTML bankaktifitas.html / Excel / CSV / teks biasa).
     *
     * @return array<int, array{kode: ?string, nama_aktivitas: string, menit: int}>
     */
    public static function parseBulkLines(string $rawData, int $defaultMenit = 30): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $rawData) ?: [];
        $parsed = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // Abaikan baris header tabel
            $lower = mb_strtolower($line);
            if (preg_match('/^(no|kode|nama\s*aktivitas|uraian|aktivitas)[\t;|,]/i', $lower) || $lower === 'nama aktivitas') {
                continue;
            }

            $kode = null;
            $nama = '';
            $menit = $defaultMenit;

            $delimiter = null;
            if (str_contains($line, "\t")) {
                $delimiter = "\t";
            } elseif (str_contains($line, '|')) {
                $delimiter = '|';
            } elseif (str_contains($line, ';')) {
                $delimiter = ';';
            }

            if ($delimiter !== null) {
                $cols = array_values(array_filter(array_map('trim', explode($delimiter, $line)), fn ($c) => $c !== ''));
                if (count($cols) >= 3) {
                    $last = end($cols);
                    if (preg_match('/(\d+)\s*(menit|mnt)?$/i', (string) $last, $m)) {
                        $menit = max(1, (int) $m[1]);
                        array_pop($cols);
                    }
                    if (count($cols) >= 2 && mb_strlen($cols[0]) <= 20) {
                        $kode = trim($cols[0], " .\t");
                        array_shift($cols);
                    }
                    $nama = implode(' ', $cols);
                } elseif (count($cols) === 2) {
                    if (preg_match('/^(\d+)\s*(menit|mnt)?$/i', $cols[1], $m)) {
                        $nama = $cols[0];
                        $menit = max(1, (int) $m[1]);
                    } elseif (mb_strlen($cols[0]) <= 15 && mb_strlen($cols[1]) > 15) {
                        $kode = trim($cols[0], " .\t");
                        $nama = $cols[1];
                    } else {
                        $nama = $cols[0] . ' - ' . $cols[1];
                    }
                } elseif (count($cols) === 1) {
                    $nama = $cols[0];
                }
            } else {
                $nama = $line;
                if (preg_match('/^(\d+)[.)\s]+(.+)$/', $nama, $m)) {
                    $kode = $m[1];
                    $nama = trim($m[2]);
                }
                if (preg_match('/^(.*?)(?:\s*[-–|:(]\s*|\s+)(\d+)\s*(?:menit|mnt)\)?$/i', $nama, $m)) {
                    $nama = trim($m[1], " -–|:(");
                    $menit = max(1, (int) $m[2]);
                }
            }

            $nama = trim($nama);
            if ($nama === '') {
                continue;
            }

            $parsed[] = [
                'kode' => $kode ?: null,
                'nama_aktivitas' => $nama,
                'menit' => $menit,
            ];
        }

        return $parsed;
    }
}
