<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SuratTugas extends Model
{
    use HasFactory;

    protected $table = 'surat_tugas';

    protected $fillable = [
        'nomor_surat',
        'nama_kegiatan',
        'dasar_surat',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'keterangan',
        'file_tte',
        'status',
        'sumber',
        'penandatangan_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
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
            ->withPivot(['peran_tugas', 'dibaca_at'])
            ->withTimestamps();
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

    public function kirimNotifikasiKeTim(): void
    {
        if ($this->status !== 'terbit') {
            return;
        }

        $this->loadMissing('pegawais');

        foreach ($this->pegawais as $pegawai) {
            Notification::make()
                ->title('Surat Tugas Resmi (TTE SRIKANDI)')
                ->body("Nomor {$this->nomor_surat}: {$this->nama_kegiatan}")
                ->icon('heroicon-o-document-check')
                ->success()
                ->sendToDatabase($pegawai);
        }
    }

    public static function generateNomorSurat(): string
    {
        $tahun = now()->year;
        $urutan = static::query()->whereYear('created_at', $tahun)->count() + 1;

        return sprintf('094/%03d/ST-DISPARPORA/%d', $urutan, $tahun);
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

    public function getQrTrackingBase64(): ?string
    {
        try {
            $options = new \chillerlan\QRCode\QROptions([
                'version' => \chillerlan\QRCode\QRCode::VERSION_AUTO,
                'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => \chillerlan\QRCode\QRCode::ECC_M,
                'scale' => 4,
                'imageBase64' => true,
                'imageTransparent' => false,
            ]);

            return (new \chillerlan\QRCode\QRCode($options))->render($this->getTrackingUrl());
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
