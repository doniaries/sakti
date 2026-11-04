<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanSpj extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_spj';

    protected $fillable = [
        'nomor_pengajuan',
        'user_id',
        'bidang_id',
        'kegiatan_id',
        'sub_kegiatan_id',
        'tahun_anggaran',
        'tanggal_pengajuan',
        'tanggal_batas_pengiriman',
        'total_nilai_rencana',
        'total_nilai_realisasi',
        'ppn_persen',
        'status',
        'master_toko_id',
        'nama_toko',
        'alamat_toko',
        'telepon_toko',
        'nama_bank',
        'metode_pembayaran',
        'penerima_nama',
        'penerima_identitas',
        'file_nota_penjual',
        'file_dokumentasi_barang',
        'justifikasi_selisih',
        'catatan_revisi',
        'pptk_user_id',
        'ttd_pptk',
        'ppk_user_id',
        'ttd_ppk',
        'pa_user_id',
        'ttd_pa',
        'bendahara_user_id',
        'ttd_bendahara',
        'tanggal_dicairkan',
        'nomor_bku',
    ];

    protected $casts = [
        'tahun_anggaran' => 'integer',
        'tanggal_pengajuan' => 'date',
        'tanggal_batas_pengiriman' => 'date',
        'tanggal_dicairkan' => 'date',
        'total_nilai_rencana' => 'decimal:2',
        'total_nilai_realisasi' => 'decimal:2',
        'ppn_persen' => 'decimal:2',
        'nama_bank' => \App\Enums\NamaBank::class,
    ];

    protected static function booted(): void
    {
        static::saving(function (self $spj): void {
            $bankValue = $spj->nama_bank instanceof \App\Enums\NamaBank
                ? $spj->nama_bank->value
                : $spj->nama_bank;

            // Jika master_toko_id dipilih tetapi nama_toko kosong, salin dari MasterToko
            if ($spj->master_toko_id && blank($spj->nama_toko)) {
                $toko = MasterToko::find($spj->master_toko_id);
                if ($toko) {
                    $spj->nama_toko = $toko->nama_toko;
                    $spj->penerima_nama = $spj->penerima_nama ?: $toko->nama_pemilik;
                    $spj->telepon_toko = $spj->telepon_toko ?: $toko->telepon;
                    $spj->alamat_toko = $spj->alamat_toko ?: $toko->alamat;
                    $spj->nama_bank = $spj->nama_bank ?: $toko->nama_bank;
                    $spj->penerima_identitas = $spj->penerima_identitas ?: $toko->nomor_rekening;
                }
            }

            // Sinkronkan juga perubahan kembali ke tabel master_tokos agar tersimpan untuk pemakaian berikutnya
            if (filled($spj->nama_toko)) {
                $toko = $spj->master_toko_id
                    ? MasterToko::find($spj->master_toko_id)
                    : MasterToko::where('nama_toko', $spj->nama_toko)->first();

                if ($toko) {
                    $toko->update([
                        'nama_toko' => $spj->nama_toko,
                        'nama_pemilik' => $spj->penerima_nama ?: $toko->nama_pemilik,
                        'telepon' => $spj->telepon_toko ?: $toko->telepon,
                        'alamat' => $spj->alamat_toko ?: $toko->alamat,
                        'nama_bank' => $bankValue ?: $toko->nama_bank,
                        'nomor_rekening' => $spj->penerima_identitas ?: $toko->nomor_rekening,
                    ]);
                    $spj->master_toko_id = $toko->id;
                } else {
                    $newToko = MasterToko::create([
                        'nama_toko' => $spj->nama_toko,
                        'nama_pemilik' => $spj->penerima_nama,
                        'telepon' => $spj->telepon_toko,
                        'alamat' => $spj->alamat_toko,
                        'nama_bank' => $bankValue ?: \App\Enums\NamaBank::BANK_NAGARI->value,
                        'nomor_rekening' => $spj->penerima_identitas,
                        'is_active' => true,
                    ]);
                    $spj->master_toko_id = $newToko->id;
                }
            }
        });
    }

    public function masterToko(): BelongsTo
    {
        return $this->belongsTo(MasterToko::class, 'master_toko_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(MasterKegiatan::class, 'kegiatan_id');
    }

    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(MasterSubKegiatan::class, 'sub_kegiatan_id');
    }

    public function pptk(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pptk_user_id');
    }

    public function ppk(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ppk_user_id');
    }

    public function pa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pa_user_id');
    }

    public function bendahara(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bendahara_user_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailPengajuanSpj::class, 'pengajuan_spj_id');
    }

    public function verifikasis(): HasMany
    {
        return $this->hasMany(VerifikasiSpj::class, 'pengajuan_spj_id');
    }

    public function riwayats(): HasMany
    {
        return $this->hasMany(RiwayatStatusSpj::class, 'pengajuan_spj_id')->orderBy('created_at', 'desc');
    }

    public static function generateNomorPengajuan(int|string|null $bidangId = null): string
    {
        $prefix = 'SP';
        $tahun = date('Y');
        $bulan = date('m');
        $count = static::whereYear('tanggal_pengajuan', $tahun)->count() + 1;
        $urut = str_pad((string) $count, 4, '0', STR_PAD_LEFT);

        return "{$prefix}/{$bulan}/{$tahun}/{$urut}";
    }

    public function getNomorSuratPesananAttribute(): string
    {
        $nomor = (string) $this->nomor_pengajuan;
        if (str_starts_with($nomor, 'SP/')) {
            return $nomor;
        }

        // Konversi format SPJ/YYYY/MM/XXXX menjadi SP/MM/YYYY/XXXX
        $parts = explode('/', $nomor);
        if (count($parts) === 4 && $parts[0] === 'SPJ') {
            return "SP/{$parts[2]}/{$parts[1]}/{$parts[3]}";
        }

        $bulan = $this->tanggal_pengajuan ? $this->tanggal_pengajuan->format('m') : date('m');
        $tahun = $this->tanggal_pengajuan ? $this->tanggal_pengajuan->format('Y') : date('Y');
        $urut = str_pad((string) ($this->id ?: 1), 4, '0', STR_PAD_LEFT);

        return "SP/{$bulan}/{$tahun}/{$urut}";
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft Surat Pesanan',
            'diajukan' => 'Menunggu TTD Surat Pesanan (Kabid/PPTK)',
            'menunggu_realisasi' => 'Surat Pesanan Di-TTD / Proses Kwitansi',
            'menunggu_verifikasi_pptk' => 'Kwitansi Menunggu Verifikasi & TTD PPTK',
            'revisi_pptk' => 'Revisi PPTK',
            'menunggu_verifikasi_ppk' => 'Kwitansi Menunggu Verifikasi PPK',
            'revisi_ppk' => 'Revisi PPK',
            'menunggu_persetujuan_pa' => 'Kwitansi Menunggu TTD PA (Kadis)',
            'revisi_pa' => 'Revisi PA',
            'menunggu_verifikasi_bendahara' => 'Kwitansi Menunggu Uji Kas Bendahara',
            'revisi_bendahara' => 'Revisi Bendahara',
            'disetujui' => 'Disetujui (Siap Cair)',
            'dicairkan' => 'Kwitansi Lunas & Dicairkan',
            'ditolak' => 'Ditolak Permanen',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'diajukan', 'menunggu_realisasi' => 'warning',
            'menunggu_verifikasi_pptk', 'menunggu_verifikasi_ppk', 'menunggu_persetujuan_pa', 'menunggu_verifikasi_bendahara' => 'info',
            'revisi_pptk', 'revisi_ppk', 'revisi_pa', 'revisi_bendahara', 'ditolak' => 'danger',
            'disetujui' => 'primary',
            'dicairkan' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Ambil spesimen gambar tanda tangan (Base64 URI) untuk DomPDF berdasarkan peran pejabat (pptk, ppk, pa, bendahara).
     */
    public function getSpecimenTtdBase64(string $role): ?string
    {
        $col = "ttd_{$role}";
        $relativePath = $this->{$col} ?: $this->{$role}?->file_ttd;

        if (! $relativePath) {
            return null;
        }

        $candidates = [
            storage_path('app/public/' . ltrim($relativePath, '/')),
            storage_path('app/' . ltrim($relativePath, '/')),
            public_path('storage/' . ltrim($relativePath, '/')),
            public_path(ltrim($relativePath, '/')),
        ];

        foreach ($candidates as $fullPath) {
            if (is_file($fullPath) && is_readable($fullPath)) {
                $mime = mime_content_type($fullPath) ?: 'image/png';
                $data = base64_encode((string) file_get_contents($fullPath));

                return "data:{$mime};base64,{$data}";
            }
        }

        return null;
    }
}

