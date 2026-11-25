<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\QrCodeHelper;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SuratTugas extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'surat_tugas';

    protected $fillable = [
        'nomor_surat',
        'nama_kegiatan',
        'dasar_surat',
        'dasar_surat_undangan',
        'asal_surat_undangan',
        'tanggal_surat_undangan',
        'ringkasan_isi_undangan',
        'bukti_surat_undangan',
        'tanggal_mulai',
        'tanggal_selesai',
        'jam_acara',
        'lokasi',
        'penanggung_jawab_id',
        'penanggung_jawab_ids',
        'koordinator_acara',
        'koordinator_acara_ids',
        'keterangan',
        'file_tte',
        'status',
        'sumber',
        'penandatangan_id',
        'ditandatangani_at',
        'hash_dokumen',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'tanggal_surat_undangan' => 'date',
            'ditandatangani_at' => 'datetime',
            'penanggung_jawab_ids' => 'array',
            'koordinator_acara_ids' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (blank($model->dasar_surat)) {
                $model->dasar_surat = self::defaultDasarSurat();
            }
        });

        static::saving(function (self $model): void {
            if (filled($model->file_tte)) {
                $model->status = 'terbit';
            } elseif (blank($model->status)) {
                $model->status = 'draft';
            }
        });
    }

    public function pegawais(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'surat_tugas_user')
            ->withPivot([
                'peran_tugas',
                'dibaca_at',
                'status_kehadiran',
                'catatan_kehadiran',
                'dikonfirmasi_at',
            ])
            ->withTimestamps();
    }

    public function laporans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LaporanSuratTugas::class, 'surat_tugas_id')->latest('dilaporkan_at');
    }

    public function kirimNotifikasiKehadiran(User $pelaksana, string $status, ?string $catatan = null): void
    {
        $this->loadMissing(['pegawais', 'creator', 'penandatangan', 'penanggungJawab']);

        $penanggungJawabs = filled($this->penanggung_jawab_ids)
            ? User::whereIn('id', $this->penanggung_jawab_ids)->get()
            : collect();

        $koordinators = filled($this->koordinator_acara_ids)
            ? User::whereIn('id', $this->koordinator_acara_ids)->get()
            : collect();

        $penerima = collect([$this->creator, $this->penandatangan, $this->penanggungJawab])
            ->merge($penanggungJawabs)
            ->merge($koordinators)
            ->merge($this->pegawais)
            ->filter(fn (?User $user): bool => $user !== null && $user->id !== $pelaksana->id)
            ->unique('id');

        $labelStatus = $status === 'hadir' ? 'Hadir' : 'Berhalangan';
        $body = "{$pelaksana->name} mengonfirmasi {$labelStatus} pada kegiatan: {$this->nama_kegiatan}";
        if (filled($catatan)) {
            $body .= " ({$catatan})";
        }

        foreach ($penerima as $user) {
            Notification::make()
                ->title("Konfirmasi Kehadiran: {$labelStatus}")
                ->body($body)
                ->icon($status === 'hadir' ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-circle')
                ->color($status === 'hadir' ? 'success' : 'warning')
                ->sendToDatabase($user);
        }
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id');
    }

    public function pegawaisSudahBaca(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'surat_tugas_user')
            ->wherePivotNotNull('dibaca_at');
    }

    public function penandatangan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penandatangan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tandaiDibacaOleh(?int $userId): void
    {
        if (! $userId) {
            return;
        }

        $this->pegawais()
            ->where('users.id', $userId)
            ->wherePivotNull('dibaca_at')
            ->updateExistingPivot($userId, ['dibaca_at' => now()]);
    }

    public function kirimNotifikasiPengajuan(): void
    {
        $this->loadMissing(['creator', 'penandatangan']);

        if ($this->creator && filled($this->creator->jabatan_tim_kreatif)) {
            Notification::make()
                ->title('Draf Surat Tugas Dibuat')
                ->body("Menunggu persetujuan TTE: {$this->nama_kegiatan}")
                ->icon('heroicon-o-document-text')
                ->info()
                ->sendToDatabase($this->creator);
        }

        if ($this->penandatangan) {
            Notification::make()
                ->title('Permohonan Persetujuan Surat Tugas')
                ->body("Mohon persetujuan & penerbitan Surat Tugas: {$this->nama_kegiatan}")
                ->icon('heroicon-o-pencil-square')
                ->warning()
                ->actions([
                    \Filament\Actions\Action::make('buka_persetujuan')
                        ->label('Setujui di SAKTI Mobile')
                        ->url(route('mobile.persetujuan', ['tab' => 'surat_tugas'])),
                ])
                ->sendToDatabase($this->penandatangan);
        } else {
            $approvers = User::role(['super_admin', 'admin', 'pa'])->get();
            foreach ($approvers as $approver) {
                Notification::make()
                    ->title('Permohonan Persetujuan Surat Tugas')
                    ->body("Mohon persetujuan & penerbitan Surat Tugas: {$this->nama_kegiatan}")
                    ->icon('heroicon-o-pencil-square')
                    ->warning()
                    ->actions([
                        \Filament\Actions\Action::make('buka_persetujuan')
                            ->label('Setujui di SAKTI Mobile')
                            ->url(route('mobile.persetujuan', ['tab' => 'surat_tugas'])),
                    ])
                    ->sendToDatabase($approver);
            }
        }
    }

    public function kirimNotifikasiKeTim(): void
    {
        if ($this->status !== 'terbit') {
            return;
        }

        $this->loadMissing(['pegawais', 'creator', 'penandatangan']);

        $penerima = collect([$this->creator, $this->penandatangan])
            ->merge($this->pegawais)
            ->filter(fn (?User $user): bool => $user !== null && filled($user->jabatan_tim_kreatif))
            ->unique('id');

        foreach ($penerima as $user) {
            Notification::make()
                ->title("Surat Tugas Tim Kreatif untuk {$user->name}")
                ->viewData(['surat_tugas_id' => $this->id])
                ->body("Nomor {$this->nomor_surat}: {$this->nama_kegiatan}")
                ->icon('heroicon-o-document-check')
                ->success()
                ->sendToDatabase($user);
        }
    }

    public static function generateNomorSurat(?\Carbon\CarbonInterface $tanggal = null): string
    {
        $date = $tanggal ? \Carbon\Carbon::instance($tanggal) : now();
        $tahun = (int) $date->year;
        $bulanRomawi = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ][(int) $date->month] ?? 'I';

        // Ambil nomor urut tertinggi pada tahun berjalan agar selalu berlanjut walau ganti bulan
        $maxUrutan = 0;
        $existingNumbers = static::query()
            ->whereNotNull('nomor_surat')
            ->where(function ($q) use ($tahun): void {
                $q->where('nomor_surat', 'like', "ST/%/TK/%/{$tahun}")
                    ->orWhere('nomor_surat', 'like', "ST/%/TK/{$tahun}");
            })
            ->pluck('nomor_surat');

        foreach ($existingNumbers as $nomor) {
            if (preg_match('/^ST\/(\d+)\/TK\//', (string) $nomor, $matches)) {
                $maxUrutan = max($maxUrutan, (int) $matches[1]);
            }
        }

        $urutanBerikutnya = $maxUrutan + 1;

        return sprintf('ST/%04d/TK/%s/%d', $urutanBerikutnya, $bulanRomawi, $tahun);
    }

    public static function defaultDasarSurat(): string
    {
        return 'Keputusan Kepala Dinas Pariwisata Pemuda dan Olahraga Kabupaten Sijunjung Nomor: 188.45/28/KPTS-DISPARPORA/2026 tentang Pembentukan Tim Kreatif Publikasi dan Promosi Dinas Pariwisata Pemuda dan Olahraga Kabupaten Sijunjung Tahun 2026.';
    }

    public function getTrackingUrl(): string
    {
        $kode = rtrim(strtr(base64_encode((string) $this->id), '+/', '-_'), '=');

        return route('surat-tugas.verifikasi', ['kode' => $kode]);
    }

    public function hitungHashDokumen(): string
    {
        $payload = implode('|', [
            $this->id,
            $this->nomor_surat ?? '',
            $this->nama_kegiatan,
            $this->tanggal_mulai?->format('Y-m-d') ?? '',
            $this->tanggal_selesai?->format('Y-m-d') ?? '',
            $this->penandatangan_id ?? '',
            $this->ditandatangani_at?->toIso8601String() ?? '',
        ]);

        return hash('sha256', $payload);
    }

    public function tandatanganiDiSakti(?int $penandatanganId = null): void
    {
        if ($penandatanganId) {
            $this->penandatangan_id = $penandatanganId;
        }

        if (blank($this->nomor_surat)) {
            $this->nomor_surat = static::generateNomorSurat($this->ditandatangani_at ?? now());
        }

        $this->ditandatangani_at = $this->ditandatangani_at ?? now();
        $this->status = 'terbit';
        $this->sumber = 'sakti';
        $this->hash_dokumen = $this->hitungHashDokumen();
        $this->save();

        $this->kirimNotifikasiKeTim();
    }

    public function getQrTrackingBase64(): ?string
    {
        return QrCodeHelper::generateWithGeoparkLogo($this->getTrackingUrl());
    }
}
