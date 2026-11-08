<?php

declare(strict_types=1);

namespace App\Services\Laporan;

use App\Models\DetailPengajuanSpj;
use App\Models\KodeRekening;
use App\Models\MasterKegiatan;
use App\Models\MasterSubKegiatan;
use App\Models\PengajuanSpj;
use App\Models\RincianAnggaranBelanja;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class LrfkCalculationService
{
    /**
     * Hitung Target ROPK Standar Kumulatif s/d Bulan Tertentu (1 - 12)
     * Mengikuti pola Kurva S kas anggaran daerah (Kabupaten Sijunjung):
     * Triwulan I   (Jan: 5%, Feb: 10%, Mar: 20%)
     * Triwulan II  (Apr: 30%, Mei: 40%, Jun: 50%)
     * Triwulan III (Jul: 60%, Agu: 70%, Sep: 80%)
     * Triwulan IV  (Okt: 85%, Nov: 95%, Des: 100%)
     */
    public function getTargetRopkFisikPersen(int $bulan): float
    {
        $bulan = max(1, min(12, $bulan));

        $targetMap = [
            1 => 5.0,
            2 => 10.0,
            3 => 20.0,
            4 => 30.0,
            5 => 40.0,
            6 => 50.0,
            7 => 60.0,
            8 => 70.0,
            9 => 80.0,
            10 => 85.0,
            11 => 95.0,
            12 => 100.0,
        ];

        return $targetMap[$bulan] ?? 100.0;
    }

    /**
     * 1. Matriks Utama LRFK per Kegiatan
     * Sesuai Sheet 'Sep 2026' / LRFK Bulanan (Gambar 2 & Rumus Gambar 1 & 5)
     */
    public function getMatriksLrfkKegiatan(int $tahun, int $bulan, ?int $bidangId = null): array
    {
        $batasTanggal = Carbon::create($tahun, $bulan)->endOfMonth()->toDateString();
        $targetFisikPersen = $this->getTargetRopkFisikPersen($bulan);

        $kegiatanQuery = MasterKegiatan::query()
            ->where('tahun_anggaran', $tahun)
            ->with(['subKegiatans.rincianAnggarans']);

        if ($bidangId) {
            $kegiatanQuery->where('bidang_id', $bidangId);
        }

        $kegiatans = $kegiatanQuery->orderBy('kode_kegiatan')->get();
        $rows = [];

        $totalPaguDpa = 0.0;
        $totalTargetKeuanganRp = 0.0;
        $totalRealisasiKeuanganRp = 0.0;
        $totalFisikTertimbangSemua = 0.0;

        foreach ($kegiatans as $keg) {
            $pagu = (float) $keg->pagu_anggaran;
            if ($pagu <= 0) {
                // Alternatif ambil sum pagu RKA jika pagu kegiatan belum diset manual
                $pagu = (float) $keg->subKegiatans->sum(fn ($s) => $s->rincianAnggarans->sum('pagu_total'));
            }

            // Realisasi Keuangan s/d Bulan Laporan
            $realisasiKeuRp = (float) PengajuanSpj::query()
                ->where('kegiatan_id', $keg->id)
                ->where('status', 'dicairkan')
                ->whereDate('tanggal_dicairkan', '<=', $batasTanggal)
                ->sum('total_nilai_realisasi');

            // Jika total_nilai_realisasi masih kosong/0, gunakan total_nilai_rencana SPJ yang dicairkan
            if ($realisasiKeuRp <= 0) {
                $realisasiKeuRp = (float) PengajuanSpj::query()
                    ->where('kegiatan_id', $keg->id)
                    ->where('status', 'dicairkan')
                    ->whereDate('tanggal_dicairkan', '<=', $batasTanggal)
                    ->sum('total_nilai_rencana');
            }

            // Realisasi Fisik Otomatis (Metode Pembobotan Proporsional Item RKA)
            $realisasiFisikPersen = 0.0;
            $itemsRka = $keg->subKegiatans->flatMap->rincianAnggarans;
            $totalPaguItem = (float) $itemsRka->sum('pagu_total');

            if ($totalPaguItem > 0) {
                foreach ($itemsRka as $item) {
                    $bobotItem = ((float) $item->pagu_total / $totalPaguItem);

                    // Kuantitas yang sudah dicairkan s/d batas tanggal
                    $volCair = (float) DetailPengajuanSpj::query()
                        ->where('rincian_anggaran_belanja_id', $item->id)
                        ->whereHas('pengajuanSpj', fn ($q) => $q->where('status', 'dicairkan')->whereDate('tanggal_dicairkan', '<=', $batasTanggal))
                        ->sum('kuantitas');

                    $progresItem = (float) $item->volume > 0
                        ? min(1.0, $volCair / (float) $item->volume)
                        : ((float) $item->nilai_realisasi > 0 ? 1.0 : 0.0);

                    $realisasiFisikPersen += ($bobotItem * $progresItem * 100.0);
                }
            } else {
                // Jika rincian RKA belum terinput detail, gunakan proporsi realisasi keuangan sebagai baseline
                $realisasiFisikPersen = $pagu > 0 ? min(100.0, ($realisasiKeuRp / $pagu) * 100.0) : 0.0;
            }

            $realisasiFisikPersen = min(100.0, round($realisasiFisikPersen, 2));

            // Perhitungan Indikator LRFK
            $capaianFisikPersen = $targetFisikPersen > 0
                ? round(($realisasiFisikPersen / $targetFisikPersen) * 100.0, 2)
                : 0.0;

            $targetKeuRp = ($targetFisikPersen / 100.0) * $pagu;
            $targetKeuPersen = $targetFisikPersen;

            $realisasiKeuPersen = $pagu > 0 ? round(($realisasiKeuRp / $pagu) * 100.0, 2) : 0.0;

            $serapanAnggaranPersen = $targetKeuRp > 0
                ? round(($realisasiKeuRp / $targetKeuRp) * 100.0, 2)
                : 0.0;

            $rows[] = [
                'id' => $keg->id,
                'kode' => $keg->kode_kegiatan,
                'nama' => $keg->nama_kegiatan,
                'pagu' => $pagu,
                'target_fisik' => $targetFisikPersen,
                'realisasi_fisik' => $realisasiFisikPersen,
                'capaian_fisik' => $capaianFisikPersen,
                'target_keu_rp' => $targetKeuRp,
                'target_keu_persen' => $targetKeuPersen,
                'realisasi_keu_rp' => $realisasiKeuRp,
                'realisasi_keu_persen' => $realisasiKeuPersen,
                'serapan_anggaran' => $serapanAnggaranPersen,
                'keterangan' => $realisasiKeuPersen >= 100 ? 'Selesai 100%' : ($realisasiKeuRp > 0 ? 'On Progress' : 'Belum Ada Realisasi'),
            ];

            $totalPaguDpa += $pagu;
            $totalTargetKeuanganRp += $targetKeuRp;
            $totalRealisasiKeuanganRp += $realisasiKeuRp;
        }

        // Rata-rata bobot fisik keseluruhan
        $totalKegiatanCount = count($rows);
        $avgRealisasiFisik = $totalKegiatanCount > 0 ? round(collect($rows)->avg('realisasi_fisik'), 2) : 0.0;
        $avgCapaianFisik = $targetFisikPersen > 0 ? round(($avgRealisasiFisik / $targetFisikPersen) * 100.0, 2) : 0.0;
        $totalSerapanAnggaran = $totalTargetKeuanganRp > 0 ? round(($totalRealisasiKeuanganRp / $totalTargetKeuanganRp) * 100.0, 2) : 0.0;
        $totalPersenRealisasiKeu = $totalPaguDpa > 0 ? round(($totalRealisasiKeuanganRp / $totalPaguDpa) * 100.0, 2) : 0.0;

        return [
            'rows' => $rows,
            'summary' => [
                'total_pagu' => $totalPaguDpa,
                'target_fisik' => $targetFisikPersen,
                'realisasi_fisik' => $avgRealisasiFisik,
                'capaian_fisik' => $avgCapaianFisik,
                'target_keu_rp' => $totalTargetKeuanganRp,
                'target_keu_persen' => $targetFisikPersen,
                'realisasi_keu_rp' => $totalRealisasiKeuanganRp,
                'realisasi_keu_persen' => $totalPersenRealisasiKeu,
                'serapan_anggaran' => $totalSerapanAnggaran,
            ],
        ];
    }

    /**
     * 2. Lampiran 1: Laporan Realisasi Belanja per Jenis Akun Standar
     * Sesuai Sheet 1 'Rincian Belanja' (Gambar 4)
     * (5.1 Belanja Operasi, 5.2 Belanja Modal, 5.3 Tak Terduga, 5.4 Transfer)
     */
    public function getLampiran1JenisBelanja(int $tahun, int $bulan, ?int $bidangId = null): array
    {
        $batasTanggal = Carbon::create($tahun, $bulan)->endOfMonth()->toDateString();

        // Kategori Baku Sesuai Lampiran 1 (Permendagri / LRFK Kabupaten Sijunjung)
        $kategoriDef = [
            '5.1' => [
                'kode' => '1',
                'uraian' => 'BELANJA OPERASI',
                'sub' => [
                    '5.1.01' => 'Belanja Pegawai',
                    '5.1.02' => 'Belanja Barang dan Jasa',
                    '5.1.05' => 'Belanja Hibah',
                ],
            ],
            '5.2' => [
                'kode' => '2',
                'uraian' => 'BELANJA MODAL',
                'sub' => [
                    '5.2.01' => 'Belanja Modal Tanah',
                    '5.2.02' => 'Belanja Modal Peralatan dan Mesin',
                    '5.2.03' => 'Belanja Modal Gedung dan Bangunan',
                    '5.2.04' => 'Belanja Modal Jalan, Irigasi dan Jaringan',
                    '5.2.05' => 'Belanja Modal Aset Tetap Lainnya',
                    '5.2.06' => 'Belanja Modal Aset Lainnya',
                ],
            ],
            '5.3' => [
                'kode' => '3',
                'uraian' => 'BELANJA TAK TERDUGA',
                'sub' => [],
            ],
            '5.4' => [
                'kode' => '4',
                'uraian' => 'BELANJA TRANSFER',
                'sub' => [
                    '5.4.01' => 'Belanja Bagi Hasil',
                    '5.4.02' => 'Belanja Bantuan Keuangan',
                ],
            ],
        ];

        $rekap = [];
        $grandTotalPagu = 0.0;
        $grandTotalRealisasi = 0.0;

        foreach ($kategoriDef as $prefixKelompok => $kelompok) {
            $kelompokPagu = 0.0;
            $kelompokRealisasi = 0.0;
            $subRows = [];

            if (empty($kelompok['sub'])) {
                // Kategori tanpa sub-rincian (misal 5.3 Belanja Tak Terduga)
                $paguQuery = RincianAnggaranBelanja::query()
                    ->where('tahun_anggaran', $tahun)
                    ->whereHas('kodeRekening', fn ($q) => $q->where('kode_lengkap', 'like', "{$prefixKelompok}%"));
                if ($bidangId) {
                    $paguQuery->where('bidang_id', $bidangId);
                }
                $kelompokPagu = (float) $paguQuery->sum('pagu_total');

                $realisasiQuery = DetailPengajuanSpj::query()
                    ->whereHas('kodeRekening', fn ($q) => $q->where('kode_lengkap', 'like', "{$prefixKelompok}%"))
                    ->whereHas('pengajuanSpj', function ($q) use ($tahun, $batasTanggal, $bidangId) {
                        $q->where('tahun_anggaran', $tahun)
                            ->where('status', 'dicairkan')
                            ->whereDate('tanggal_dicairkan', '<=', $batasTanggal);
                        if ($bidangId) {
                            $q->where('bidang_id', $bidangId);
                        }
                    });
                $kelompokRealisasi = (float) $realisasiQuery->sum('subtotal');
            } else {
                foreach ($kelompok['sub'] as $prefixSub => $namaSub) {
                    $altPrefix = str_replace('.0', '.', $prefixSub);

                    // Pagu DPA RKA dengan kode rekening prefix bersangkutan
                    $paguQuery = RincianAnggaranBelanja::query()
                        ->where('tahun_anggaran', $tahun)
                        ->whereHas('kodeRekening', fn ($q) => $q->where(fn ($w) => $w->where('kode_lengkap', 'like', "{$prefixSub}%")->orWhere('kode_lengkap', 'like', "{$altPrefix}%")));

                    if ($bidangId) {
                        $paguQuery->where('bidang_id', $bidangId);
                    }
                    $subPagu = (float) $paguQuery->sum('pagu_total');

                    // Realisasi dari SPJ Dicairkan
                    $realisasiQuery = DetailPengajuanSpj::query()
                        ->whereHas('kodeRekening', fn ($q) => $q->where(fn ($w) => $w->where('kode_lengkap', 'like', "{$prefixSub}%")->orWhere('kode_lengkap', 'like', "{$altPrefix}%")))
                        ->whereHas('pengajuanSpj', function ($q) use ($tahun, $batasTanggal, $bidangId) {
                            $q->where('tahun_anggaran', $tahun)
                                ->where('status', 'dicairkan')
                                ->whereDate('tanggal_dicairkan', '<=', $batasTanggal);
                            if ($bidangId) {
                                $q->where('bidang_id', $bidangId);
                            }
                        });

                    $subRealisasi = (float) $realisasiQuery->sum('subtotal');
                    $subPersen = $subPagu > 0 ? round(($subRealisasi / $subPagu) * 100.0, 2) : 0.0;

                    $subRows[] = [
                        'prefix' => $prefixSub,
                        'nama' => $namaSub,
                        'anggaran' => $subPagu,
                        'realisasi' => $subRealisasi,
                        'persen' => $subPersen,
                    ];

                    $kelompokPagu += $subPagu;
                    $kelompokRealisasi += $subRealisasi;
                }
            }

            $kelompokPersen = $kelompokPagu > 0 ? round(($kelompokRealisasi / $kelompokPagu) * 100.0, 2) : 0.0;

            $rekap[] = [
                'nomor' => $kelompok['kode'],
                'uraian' => $kelompok['uraian'],
                'anggaran' => $kelompokPagu,
                'realisasi' => $kelompokRealisasi,
                'persen' => $kelompokPersen,
                'sub_items' => $subRows,
            ];

            $grandTotalPagu += $kelompokPagu;
            $grandTotalRealisasi += $kelompokRealisasi;
        }

        $grandPersen = $grandTotalPagu > 0 ? round(($grandTotalRealisasi / $grandTotalPagu) * 100.0, 2) : 0.0;

        return [
            'kelompok' => $rekap,
            'total_anggaran' => $grandTotalPagu,
            'total_realisasi' => $grandTotalRealisasi,
            'persen' => $grandPersen,
        ];
    }

    /**
     * 3. Laporan Kemajuan Fisik Pekerjaan (Kontraktual / Belanja Modal / Pengadaan)
     * Sesuai Sheet 2 'Kemajuan Fisik Pekerjaan' (Gambar 3)
     */
    public function getKemajuanPekerjaanFisik(int $tahun, int $bulan, ?int $bidangId = null): array
    {
        $batasTanggal = Carbon::create($tahun, $bulan)->endOfMonth()->toDateString();
        $targetFisik = $this->getTargetRopkFisikPersen($bulan);

        // Ambil transaksi SPJ yang melibatkan rekanan toko / belanja modal / kontraktual
        $spjQuery = PengajuanSpj::query()
            ->with(['kegiatan', 'subKegiatan', 'masterToko'])
            ->where('tahun_anggaran', $tahun)
            ->whereNotNull('nama_toko')
            ->where(function (Builder $q) {
                $q->whereIn('metode_pengadaan', ['e_katalog', 'non_katalog'])
                    ->orWhere('jenis_belanja', 'barang_jasa');
            });

        if ($bidangId) {
            $spjQuery->where('bidang_id', $bidangId);
        }

        $spjs = $spjQuery->orderBy('tanggal_pengajuan', 'desc')->get();
        $rows = [];
        $totalPagu = 0.0;
        $totalKontrak = 0.0;
        $totalSisa = 0.0;
        $totalRealisasiKeu = 0.0;

        foreach ($spjs as $index => $spj) {
            $nilaiKontrak = (float) ($spj->total_nilai_realisasi ?: $spj->total_nilai_rencana);
            // Pagu diambil dari pagu sub kegiatan atau nilai kontrak + margin
            $paguPekerjaan = $spj->subKegiatan?->rincianAnggarans()->sum('pagu_total') ?: $nilaiKontrak;
            $sisaDana = max(0, $paguPekerjaan - $nilaiKontrak);

            // Cek apakah SPJ masuk dalam periode bulan laporan
            $tglAcuan = $spj->tanggal_dicairkan ?: $spj->tanggal_pengajuan;
            $isDalamPeriode = $tglAcuan ? (Carbon::parse($tglAcuan)->toDateString() <= $batasTanggal) : true;

            // Realisasi Keuangan (Kolom 13 & 14):
            // Sesuai format resmi Laporan Kemajuan Pekerjaan Kabupaten Sijunjung,
            // SPJ belanja/pekerjaan yang terlaksana menampilkan nilai realisasi belanja aktualnya.
            if ($isDalamPeriode && !in_array($spj->status, ['ditolak', 'batal'])) {
                $realisasiKeuRp = (float) ($spj->total_nilai_realisasi > 0 ? $spj->total_nilai_realisasi : $nilaiKontrak);
            } elseif ($spj->status === 'dicairkan') {
                $realisasiKeuRp = (float) ($spj->total_nilai_realisasi > 0 ? $spj->total_nilai_realisasi : $nilaiKontrak);
            } else {
                $realisasiKeuRp = 0.0;
            }

            $realisasiKeuPersen = $nilaiKontrak > 0 ? round(($realisasiKeuRp / $nilaiKontrak) * 100.0, 2) : 0.0;

            // Bobot Fisik: 100% jika pekerjaan/pengadaan barang sudah terealisasi/berjalan
            $bobotRencana = 100.0;
            $bobotRealisasi = match ($spj->status) {
                'dicairkan', 'disetujui', 'menunggu_verifikasi_bendahara', 'menunggu_persetujuan_pa', 'menunggu_verifikasi_ppk', 'menunggu_verifikasi_pptk' => 100.0,
                default => ($spj->total_nilai_realisasi > 0 ? 100.0 : $targetFisik),
            };

            $deviasi = round($bobotRealisasi - $bobotRencana, 2);

            $rows[] = [
                'no' => $index + 1,
                'nama_pekerjaan' => $spj->nomor_pengajuan . ' - Belanja melalui ' . ($spj->nama_toko ?: 'Penyedia'),
                'kegiatan' => $spj->kegiatan?->nama_kegiatan,
                'pagu' => $paguPekerjaan,
                'nilai_kontrak' => $nilaiKontrak,
                'sisa_dana' => $sisaDana,
                'penyedia' => $spj->nama_toko,
                'nomor_kontrak' => $spj->nomor_pengajuan,
                'tgl_mulai' => $spj->tanggal_pengajuan ? Carbon::parse($spj->tanggal_pengajuan)->format('d/m/Y') : '-',
                'tgl_selesai' => $spj->tanggal_batas_pengiriman ? Carbon::parse($spj->tanggal_batas_pengiriman)->format('d/m/Y') : '-',
                'bobot_rencana' => $bobotRencana,
                'bobot_realisasi' => $bobotRealisasi,
                'deviasi' => $deviasi,
                'realisasi_keu_rp' => $realisasiKeuRp,
                'realisasi_keu_persen' => $realisasiKeuPersen,
                'status' => $spj->status_label,
            ];

            $totalPagu += $paguPekerjaan;
            $totalKontrak += $nilaiKontrak;
            $totalSisa += $sisaDana;
            $totalRealisasiKeu += $realisasiKeuRp;
        }

        return [
            'rows' => $rows,
            'summary' => [
                'total_pagu' => $totalPagu,
                'total_kontrak' => $totalKontrak,
                'total_sisa' => $totalSisa,
                'total_realisasi_keu' => $totalRealisasiKeu,
            ],
        ];
    }

    /**
     * 4. Laporan Pertanggungjawaban Fungsional (LPJ Fungsional Bendahara)
     * Mengacu Permendagri 77/2020:
     * - Realisasi belanja per Kode Rekening Akun (Lalu, Ini, Total s/d Ini, Sisa)
     * - Register SP2D dan Kas
     * - Rekapitulasi Pajak (PPN & PPh22)
     */
    public function getLpjFungsional(int $tahun, int $bulan): array
    {
        $awalBulanIni = Carbon::create($tahun, $bulan)->startOfMonth()->toDateString();
        $akhirBulanIni = Carbon::create($tahun, $bulan)->endOfMonth()->toDateString();

        // 1. Rekening Belanja
        $kodeRekenings = KodeRekening::query()
            ->whereHas('rincianAnggarans', fn ($q) => $q->where('tahun_anggaran', $tahun))
            ->orderBy('kode_lengkap')
            ->get();

        $rows = [];
        $totalPagu = 0.0;
        $totalLalu = 0.0;
        $totalIni = 0.0;
        $totalSdIni = 0.0;
        $totalSisa = 0.0;

        foreach ($kodeRekenings as $rek) {
            $pagu = (float) $rek->rincianAnggarans()->where('tahun_anggaran', $tahun)->sum('pagu_total');

            // Realisasi s/d Bulan Lalu
            $realisasiLalu = (float) DetailPengajuanSpj::query()
                ->where('kode_rekening_id', $rek->id)
                ->whereHas('pengajuanSpj', fn ($q) => $q->where('tahun_anggaran', $tahun)
                    ->where('status', 'dicairkan')
                    ->whereDate('tanggal_dicairkan', '<', $awalBulanIni))
                ->sum('subtotal');

            // Realisasi Bulan Ini
            $realisasiIni = (float) DetailPengajuanSpj::query()
                ->where('kode_rekening_id', $rek->id)
                ->whereHas('pengajuanSpj', fn ($q) => $q->where('tahun_anggaran', $tahun)
                    ->where('status', 'dicairkan')
                    ->whereBetween('tanggal_dicairkan', [$awalBulanIni, $akhirBulanIni]))
                ->sum('subtotal');

            $sdBulanIni = $realisasiLalu + $realisasiIni;
            $sisaAnggaran = max(0, $pagu - $sdBulanIni);
            $persen = $pagu > 0 ? round(($sdBulanIni / $pagu) * 100.0, 2) : 0.0;

            $rows[] = [
                'kode' => $rek->kode_lengkap,
                'uraian' => $rek->uraian,
                'pagu' => $pagu,
                'realisasi_lalu' => $realisasiLalu,
                'realisasi_ini' => $realisasiIni,
                'total_sd_ini' => $sdBulanIni,
                'sisa' => $sisaAnggaran,
                'persen' => $persen,
            ];

            $totalPagu += $pagu;
            $totalLalu += $realisasiLalu;
            $totalIni += $realisasiIni;
            $totalSdIni += $sdBulanIni;
            $totalSisa += $sisaAnggaran;
        }

        // 2. Rekapitulasi Pajak SPJ
        $spjsCairBulanIni = PengajuanSpj::query()
            ->where('tahun_anggaran', $tahun)
            ->where('status', 'dicairkan')
            ->whereDate('tanggal_dicairkan', '<=', $akhirBulanIni)
            ->get();

        $ppnTotal = (float) $spjsCairBulanIni->sum('nilai_ppn');
        $pph22Total = (float) $spjsCairBulanIni->sum('nilai_pph22');
        $totalPajak = (float) $spjsCairBulanIni->sum('total_pajak');
        $nilaiBersihTotal = (float) $spjsCairBulanIni->sum('nilai_bersih');

        return [
            'rekening_rows' => $rows,
            'summary' => [
                'total_pagu' => $totalPagu,
                'total_lalu' => $totalLalu,
                'total_ini' => $totalIni,
                'total_sd_ini' => $totalSdIni,
                'total_sisa' => $totalSisa,
                'persen' => $totalPagu > 0 ? round(($totalSdIni / $totalPagu) * 100.0, 2) : 0.0,
            ],
            'pajak' => [
                'ppn' => $ppnTotal,
                'pph22' => $pph22Total,
                'total_pajak' => $totalPajak,
                'nilai_bersih' => $nilaiBersihTotal,
            ],
        ];
    }
}
