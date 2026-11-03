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
        'tanggal_pengajuan',
        'total_nilai_rencana',
        'total_nilai_realisasi',
        'status',
        'nama_toko',
        'alamat_toko',
        'penerima_nama',
        'penerima_identitas',
        'file_nota_penjual',
        'file_dokumentasi_barang',
        'justifikasi_selisih',
        'catatan_revisi',
        'pptk_user_id',
        'ppk_user_id',
        'pa_user_id',
        'bendahara_user_id',
        'tanggal_dicairkan',
        'nomor_bku',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_dicairkan' => 'date',
        'total_nilai_rencana' => 'decimal:2',
        'total_nilai_realisasi' => 'decimal:2',
    ];

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
        $prefix = 'SPJ';
        $tahun = date('Y');
        $bulan = date('m');
        $count = static::whereYear('tanggal_pengajuan', $tahun)->count() + 1;
        $urut = str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}/{$tahun}/{$bulan}/{$urut}";
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft Rencana',
            'diajukan' => 'Diajukan ke PPTK',
            'menunggu_realisasi' => 'Nota Pesanan Terbit / Belanja',
            'menunggu_verifikasi_pptk' => 'Menunggu Verifikasi PPTK',
            'revisi_pptk' => 'Revisi PPTK',
            'menunggu_verifikasi_ppk' => 'Menunggu Verifikasi PPK',
            'revisi_ppk' => 'Revisi PPK',
            'menunggu_persetujuan_pa' => 'Menunggu Persetujuan PA (Kadis)',
            'revisi_pa' => 'Revisi PA',
            'menunggu_verifikasi_bendahara' => 'Menunggu Uji Kas Bendahara',
            'revisi_bendahara' => 'Revisi Bendahara',
            'disetujui' => 'Disetujui (Siap Cair)',
            'dicairkan' => 'Dicairkan & Lunas',
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
}
