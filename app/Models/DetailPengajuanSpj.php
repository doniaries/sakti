<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPengajuanSpj extends Model
{
    use HasFactory;

    protected $table = 'detail_pengajuan_spj';

    protected $fillable = [
        'pengajuan_spj_id',
        'rincian_anggaran_belanja_id',
        'kode_rekening_id',
        'master_barang_id',
        'nama_item_belanja',
        'satuan',
        'kuantitas',
        'harga_satuan',
        'subtotal',
        'kuantitas_realisasi',
        'harga_realisasi',
        'subtotal_realisasi',
    ];

    protected $casts = [
        'kuantitas' => 'integer',
        'kuantitas_realisasi' => 'integer',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'harga_realisasi' => 'decimal:2',
        'subtotal_realisasi' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saved(function (DetailPengajuanSpj $detail) {
            $detail->recalculateParentTotals();
        });

        static::deleted(function (DetailPengajuanSpj $detail) {
            $detail->recalculateParentTotals();
        });
    }

    public function recalculateParentTotals(): void
    {
        $spj = $this->pengajuanSpj;
        if ($spj) {
            $totalRencana = (float) $spj->details()->sum('subtotal');
            $totalRealisasi = (float) $spj->details()->sum('subtotal_realisasi');
            $nominalDasar = $totalRealisasi > 0 ? $totalRealisasi : $totalRencana;

            // Pajak fleksibel: hormati pilihan manual form, hanya default jika null
            $ppnPersen = $spj->ppn_persen !== null ? (float) $spj->ppn_persen : ($nominalDasar > 2000000 ? 11.00 : 0.00);
            $pph22Persen = $spj->pph22_persen !== null ? (float) $spj->pph22_persen : ($nominalDasar > 2000000 ? 1.50 : 0.00);

            $nilaiPpn = round($nominalDasar * ($ppnPersen / 100));
            $nilaiPph22 = round($nominalDasar * ($pph22Persen / 100));
            $totalPajak = $nilaiPpn + $nilaiPph22;
            $nilaiBersih = max(0, $nominalDasar - $totalPajak);

            $spj->updateQuietly([
                'total_nilai_rencana' => $totalRencana,
                'total_nilai_realisasi' => $totalRealisasi > 0 ? $totalRealisasi : null,
                'ppn_persen' => $ppnPersen,
                'pph22_persen' => $pph22Persen,
                'nilai_ppn' => $nilaiPpn,
                'nilai_pph22' => $nilaiPph22,
                'total_pajak' => $totalPajak,
                'nilai_bersih' => $nilaiBersih,
            ]);
        }
    }

    public function rincianAnggaranBelanja(): BelongsTo
    {
        return $this->belongsTo(RincianAnggaranBelanja::class, 'rincian_anggaran_belanja_id');
    }

    public function pengajuanSpj(): BelongsTo
    {
        return $this->belongsTo(PengajuanSpj::class, 'pengajuan_spj_id');
    }

    public function kodeRekening(): BelongsTo
    {
        return $this->belongsTo(KodeRekening::class, 'kode_rekening_id');
    }

    public function masterBarang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'master_barang_id');
    }

    public function getNamaBarangAttribute(): string
    {
        if (filled($this->nama_item_belanja)) {
            return $this->nama_item_belanja;
        }

        if ($this->rincianAnggaranBelanja?->nama_item) {
            return $this->rincianAnggaranBelanja->nama_item;
        }

        return $this->masterBarang?->nama_barang ?? 'Item Belanja';
    }

    public function getSatuanNamaAttribute(): string
    {
        if (filled($this->satuan)) {
            return $this->satuan;
        }

        if ($this->rincianAnggaranBelanja?->satuan) {
            return $this->rincianAnggaranBelanja->satuan;
        }

        return $this->masterBarang?->satuan?->nama ?? 'Unit';
    }
}
