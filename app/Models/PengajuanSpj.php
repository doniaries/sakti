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
        'metode_pengadaan',
        'jenis_belanja',
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
        'pph22_persen',
        'nilai_ppn',
        'nilai_pph22',
        'total_pajak',
        'nilai_bersih',
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
        'file_ekatalog_bundel',
        'tingkat_perjalanan',
        'nomor_spt',
        'tanggal_spt',
        'maksud_perjalanan',
        'tanggal_berangkat',
        'tanggal_kembali',
        'lama_hari',
        'lokasi_tujuan',
        'master_tujuan_bbm_id',
        'jumlah_liter_bbm',
        'harga_per_liter',
        'dokumen_syarat_files',
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
        'tanggal_spt' => 'date',
        'tanggal_berangkat' => 'date',
        'tanggal_kembali' => 'date',
        'tanggal_dicairkan' => 'date',
        'lama_hari' => 'integer',
        'total_nilai_rencana' => 'decimal:2',
        'total_nilai_realisasi' => 'decimal:2',
        'ppn_persen' => 'decimal:2',
        'pph22_persen' => 'decimal:2',
        'nilai_ppn' => 'decimal:2',
        'nilai_pph22' => 'decimal:2',
        'total_pajak' => 'decimal:2',
        'nilai_bersih' => 'decimal:2',
        'jumlah_liter_bbm' => 'decimal:2',
        'harga_per_liter' => 'decimal:2',
        'dokumen_syarat_files' => 'array',
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

            // Otomasi Pajak Belanja Barang:
            // Ambang batas: > Rp 2.000.000 dikenakan PPN 11% dan PPh 22 1.5%
            $nominalDasar = (float) ($spj->total_nilai_realisasi > 0 ? $spj->total_nilai_realisasi : $spj->total_nilai_rencana);
            if ($nominalDasar > 2000000) {
                if ((float) $spj->ppn_persen == 0) {
                    $spj->ppn_persen = 11.00;
                }
                if ((float) $spj->pph22_persen == 0) {
                    $spj->pph22_persen = 1.50;
                }
            }

            $ppnRate = (float) ($spj->ppn_persen ?? 0);
            $pphRate = (float) ($spj->pph22_persen ?? 0);

            $spj->nilai_ppn = round($nominalDasar * ($ppnRate / 100));
            $spj->nilai_pph22 = round($nominalDasar * ($pphRate / 100));
            $spj->total_pajak = $spj->nilai_ppn + $spj->nilai_pph22;
            $spj->nilai_bersih = max(0, $nominalDasar - $spj->total_pajak);
        });
    }

    public function masterTujuanBbm(): BelongsTo
    {
        return $this->belongsTo(MasterTujuanBbm::class, 'master_tujuan_bbm_id');
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

    public static function generateNomorPengajuan(int|string|null $bidangId = null, ?int $tahun = null, ?string $tanggal = null): string
    {
        $prefix = 'SP';
        $tahunAnggaran = $tahun ?: (int) date('Y');
        $bulan = $tanggal ? date('m', strtotime($tanggal)) : date('m');

        $inisial = 'SEKR';
        if ($bidangId) {
            $bidang = Bidang::find($bidangId);
            if ($bidang) {
                $inisial = $bidang->inisial;
            }
        }

        // Ambil nomor urut tertinggi khusus untuk bidang ini pada tahun anggaran berjalan (terpisah per bidang)
        $query = static::whereYear('tanggal_pengajuan', $tahunAnggaran);
        if ($bidangId) {
            $query->where('bidang_id', $bidangId);
        }

        $existingNumbers = $query->pluck('nomor_pengajuan')->all();
        $maxUrut = 0;
        foreach ($existingNumbers as $num) {
            $parts = explode('/', (string) $num);
            $last = end($parts);
            if (is_numeric($last)) {
                $maxUrut = max($maxUrut, (int) $last);
            }
        }

        $count = max($query->count(), $maxUrut) + 1;
        $urut = str_pad((string) $count, 4, '0', STR_PAD_LEFT);

        return "{$prefix}/{$inisial}/{$bulan}/{$tahunAnggaran}/{$urut}";
    }

    public function getNomorSuratPesananAttribute(): string
    {
        if (filled($this->nomor_pengajuan)) {
            return (string) $this->nomor_pengajuan;
        }

        return static::generateNomorPengajuan(
            $this->bidang_id,
            (int) ($this->tahun_anggaran ?? date('Y')),
            $this->tanggal_pengajuan?->toDateString()
        );
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

