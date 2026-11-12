<?php

declare(strict_types=1);

namespace App\Services\Laporan;

use App\Models\DetailPengajuanSpj;
use App\Models\Instansi;
use App\Models\KodeRekening;
use App\Models\MasterKegiatan;
use App\Models\MasterProgram;
use App\Models\MasterSubKegiatan;
use App\Models\PengaturanAplikasi;
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
     * Format 14 Kolom Standar SIPD-RI Penatausahaan (Permendagri 77/2020)
     */
    public function getLpjFungsional(
        int $tahun,
        int $bulan,
        ?int $bidangId = null,
        ?int $kegiatanId = null,
        ?int $subKegiatanId = null
    ): array {
        $awalBulanIni = Carbon::create($tahun, $bulan)->startOfMonth()->toDateString();
        $akhirBulanIni = Carbon::create($tahun, $bulan)->endOfMonth()->toDateString();

        $standardTitles = [
            '5' => 'BELANJA DAERAH',
            '5.1' => 'BELANJA OPERASI',
            '5.1.01' => 'Belanja Pegawai',
            '5.1.01.01' => 'Belanja Gaji dan Tunjangan ASN',
            '5.1.01.02' => 'Belanja Tambahan Penghasilan ASN',
            '5.1.02' => 'Belanja Barang dan Jasa',
            '5.1.02.01' => 'Belanja Barang',
            '5.1.02.01.001' => 'Belanja Barang Pakai Habis',
            '5.1.02.01.002' => 'Belanja Barang Tak Habis Pakai',
            '5.1.02.01.004' => 'Belanja Aset Tetap yang Tidak Memenuhi Kriteria Kapitalisasi',
            '5.1.02.02' => 'Belanja Jasa',
            '5.1.02.02.001' => 'Belanja Jasa Kantor',
            '5.1.02.02.002' => 'Belanja Iuran Jaminan/Asuransi',
            '5.1.02.03' => 'Belanja Pemeliharaan',
            '5.1.02.03.002' => 'Belanja Pemeliharaan Peralatan dan Mesin',
            '5.1.02.03.003' => 'Belanja Pemeliharaan Gedung dan Bangunan',
            '5.1.02.04' => 'Belanja Perjalanan Dinas',
            '5.1.02.04.001' => 'Belanja Perjalanan Dinas Dalam Negeri',
            '5.1.02.04.002' => 'Belanja Perjalanan Dinas Luar Negeri',
            '5.1.05' => 'Belanja Hibah',
            '5.1.05.05' => 'Belanja Hibah kepada Badan, Lembaga, Organisasi Kemasyarakatan yang Berbadan Hukum Indonesia',
            '5.1.05.05.001' => 'Belanja Hibah kepada Badan dan Lembaga yang Bersifat Nirlaba, Sukarela dan Sosial yang Dibentuk Berdasarkan Peraturan Perundang-Undangan',
            '5.2' => 'BELANJA MODAL',
            '5.2.02' => 'Belanja Modal Peralatan dan Mesin',
            '5.2.02.18' => 'Belanja Modal Rambu-Rambu',
            '5.2.02.18.001' => 'Belanja Modal Rambu-Rambu Lalu Lintas Darat',
        ];

        // 1. Ambil detail SPJ yang dicairkan s/d akhir bulan ini (filter bidang, kegiatan, sub kegiatan)
        $spjDetails = DetailPengajuanSpj::query()
            ->whereHas('pengajuanSpj', function ($q) use ($tahun, $akhirBulanIni, $bidangId, $kegiatanId, $subKegiatanId) {
                $q->where('tahun_anggaran', $tahun)
                    ->where('status', 'dicairkan')
                    ->whereDate('tanggal_dicairkan', '<=', $akhirBulanIni)
                    ->when($bidangId, fn ($query) => $query->where('bidang_id', $bidangId))
                    ->when($kegiatanId, fn ($query) => $query->where('kegiatan_id', $kegiatanId))
                    ->when($subKegiatanId, fn ($query) => $query->where('sub_kegiatan_id', $subKegiatanId));
            })
            ->with(['pengajuanSpj', 'kodeRekening'])
            ->get();

        $spjBySubAndRek = [];
        foreach ($spjDetails as $d) {
            $subId = (int) $d->pengajuanSpj->sub_kegiatan_id;
            $rekId = (int) $d->kode_rekening_id;
            $key = "{$subId}_{$rekId}";
            if (! isset($spjBySubAndRek[$key])) {
                $spjBySubAndRek[$key] = [
                    'ls_gaji_lalu' => 0.0, 'ls_gaji_ini' => 0.0, 'ls_gaji_sd_ini' => 0.0,
                    'ls_bj_lalu' => 0.0, 'ls_bj_ini' => 0.0, 'ls_bj_sd_ini' => 0.0,
                    'up_lalu' => 0.0, 'up_ini' => 0.0, 'up_sd_ini' => 0.0,
                ];
            }

            $nominal = (float) ($d->subtotal_realisasi > 0 ? $d->subtotal_realisasi : $d->subtotal);
            $tglCair = Carbon::parse($d->pengajuanSpj->tanggal_dicairkan)->toDateString();
            $isLalu = $tglCair < $awalBulanIni;
            $kode = $d->kodeRekening?->kode_lengkap ?? '';

            $bucket = 'up';
            if (str_starts_with($kode, '5.1.01')) {
                $bucket = 'ls_gaji';
            } elseif (str_starts_with($kode, '5.2') || str_starts_with($kode, '5.1.05') || $d->pengajuanSpj->metode_pengadaan === 'e_katalog' || $nominal >= 50000000) {
                $bucket = 'ls_bj';
            }

            if ($isLalu) {
                $spjBySubAndRek[$key][$bucket . '_lalu'] += $nominal;
            } else {
                $spjBySubAndRek[$key][$bucket . '_ini'] += $nominal;
            }
            $spjBySubAndRek[$key][$bucket . '_sd_ini'] += $nominal;
        }

        // 2. Ambil data rincian anggaran belanja (RAB) tahun ini (filter bidang, kegiatan, sub kegiatan)
        $rabs = RincianAnggaranBelanja::query()
            ->where('tahun_anggaran', $tahun)
            ->when($bidangId, fn ($q) => $q->where('bidang_id', $bidangId))
            ->when($kegiatanId, fn ($q) => $q->whereHas('subKegiatan', fn ($sq) => $sq->where('kegiatan_id', $kegiatanId)))
            ->when($subKegiatanId, fn ($q) => $q->where('sub_kegiatan_id', $subKegiatanId))
            ->with(['subKegiatan.kegiatan.program', 'kodeRekening'])
            ->get();

        $leafAccountsBySub = [];
        foreach ($rabs as $rab) {
            $sub = $rab->subKegiatan;
            if (! $sub) {
                continue;
            }

            $subId = $sub->id;
            $rekId = $rab->kode_rekening_id;
            $rek = $rab->kodeRekening;
            $kode = $rek?->kode_lengkap ?? '5.1.02';
            $uraian = $rek?->uraian ?? $rab->nama_item;

            if (! isset($leafAccountsBySub[$subId])) {
                $leafAccountsBySub[$subId] = [];
            }

            if (! isset($leafAccountsBySub[$subId][$kode])) {
                $spjData = $spjBySubAndRek["{$subId}_{$rekId}"] ?? [
                    'ls_gaji_lalu' => 0.0, 'ls_gaji_ini' => 0.0, 'ls_gaji_sd_ini' => 0.0,
                    'ls_bj_lalu' => 0.0, 'ls_bj_ini' => 0.0, 'ls_bj_sd_ini' => 0.0,
                    'up_lalu' => 0.0, 'up_ini' => 0.0, 'up_sd_ini' => 0.0,
                ];

                $leafAccountsBySub[$subId][$kode] = [
                    'kode' => $kode,
                    'uraian' => $uraian,
                    'pagu' => 0.0,
                    'ls_gaji_lalu' => $spjData['ls_gaji_lalu'],
                    'ls_gaji_ini' => $spjData['ls_gaji_ini'],
                    'ls_gaji_sd_ini' => $spjData['ls_gaji_sd_ini'],
                    'ls_bj_lalu' => $spjData['ls_bj_lalu'],
                    'ls_bj_ini' => $spjData['ls_bj_ini'],
                    'ls_bj_sd_ini' => $spjData['ls_bj_sd_ini'],
                    'up_lalu' => $spjData['up_lalu'],
                    'up_ini' => $spjData['up_ini'],
                    'up_sd_ini' => $spjData['up_sd_ini'],
                ];
            }

            $leafAccountsBySub[$subId][$kode]['pagu'] += (float) $rab->pagu_total;
        }

        foreach ($leafAccountsBySub as $subId => &$accounts) {
            foreach ($accounts as $kode => &$acc) {
                $acc['total_spj'] = $acc['ls_gaji_sd_ini'] + $acc['ls_bj_sd_ini'] + $acc['up_sd_ini'];
                $acc['sisa'] = max(0, $acc['pagu'] - $acc['total_spj']);
            }
        }
        unset($accounts, $acc);

        $sumMetrics = function (array $items): array {
            $res = [
                'pagu' => 0.0,
                'ls_gaji_lalu' => 0.0, 'ls_gaji_ini' => 0.0, 'ls_gaji_sd_ini' => 0.0,
                'ls_bj_lalu' => 0.0, 'ls_bj_ini' => 0.0, 'ls_bj_sd_ini' => 0.0,
                'up_lalu' => 0.0, 'up_ini' => 0.0, 'up_sd_ini' => 0.0,
                'total_spj' => 0.0, 'sisa' => 0.0,
            ];
            foreach ($items as $item) {
                $res['pagu'] += (float) ($item['pagu'] ?? 0);
                $res['ls_gaji_lalu'] += (float) ($item['ls_gaji_lalu'] ?? 0);
                $res['ls_gaji_ini'] += (float) ($item['ls_gaji_ini'] ?? 0);
                $res['ls_gaji_sd_ini'] += (float) ($item['ls_gaji_sd_ini'] ?? 0);
                $res['ls_bj_lalu'] += (float) ($item['ls_bj_lalu'] ?? 0);
                $res['ls_bj_ini'] += (float) ($item['ls_bj_ini'] ?? 0);
                $res['ls_bj_sd_ini'] += (float) ($item['ls_bj_sd_ini'] ?? 0);
                $res['up_lalu'] += (float) ($item['up_lalu'] ?? 0);
                $res['up_ini'] += (float) ($item['up_ini'] ?? 0);
                $res['up_sd_ini'] += (float) ($item['up_sd_ini'] ?? 0);
                $res['total_spj'] += (float) ($item['total_spj'] ?? 0);
                $res['sisa'] += (float) ($item['sisa'] ?? 0);
            }
            return $res;
        };

        $allGlobalLeafs = [];
        foreach ($leafAccountsBySub as $accs) {
            foreach ($accs as $a) {
                $allGlobalLeafs[] = $a;
            }
        }

        $globalTotals = $sumMetrics($allGlobalLeafs);
        $globalTotals['total_pagu'] = $globalTotals['pagu'];
        $globalTotals['total_lalu'] = $globalTotals['ls_gaji_lalu'] + $globalTotals['ls_bj_lalu'] + $globalTotals['up_lalu'];
        $globalTotals['total_ini'] = $globalTotals['ls_gaji_ini'] + $globalTotals['ls_bj_ini'] + $globalTotals['up_ini'];
        $globalTotals['total_sd_ini'] = $globalTotals['total_spj'];
        $globalTotals['total_sisa'] = $globalTotals['sisa'];
        $globalTotals['persen'] = $globalTotals['pagu'] > 0 ? ($globalTotals['total_spj'] / $globalTotals['pagu']) * 100 : 0.0;

        $rows = [];

        // 3. Ringkasan Akun Utama (Level 0 - Bagian Atas Tabel Halaman 1)
        $summaryCodes = ['5', '5.1', '5.1.01', '5.1.02', '5.1.05', '5.2', '5.2.02'];
        foreach ($summaryCodes as $sCode) {
            $matchingLeafs = array_filter($allGlobalLeafs, fn ($item) => str_starts_with($item['kode'], $sCode));
            $sMetrics = $sumMetrics($matchingLeafs);
            $rows[] = array_merge([
                'kode' => $sCode,
                'uraian' => $standardTitles[$sCode] ?? $sCode,
                'type' => 'summary_header',
                'is_bold' => true,
                'indent' => 0,
            ], $sMetrics);
        }

        // 4. Baris Total SKPD
        $instansi = Instansi::first() ?? PengaturanAplikasi::first();
        $namaSkpd = strtoupper($instansi?->nama_dinas ?? 'DINAS PARIWISATA, PEMUDA DAN OLAH RAGA');
        $rows[] = array_merge([
            'kode' => '',
            'uraian' => $namaSkpd,
            'type' => 'skpd',
            'is_bold' => true,
            'indent' => 0,
        ], $globalTotals);

        // 5. Detail Program -> Kegiatan -> Sub Kegiatan -> Akun
        $programs = MasterProgram::query()
            ->whereHas('kegiatans.subKegiatans.rincianAnggarans', fn ($q) => $q->where('tahun_anggaran', $tahun))
            ->with(['kegiatans.subKegiatans'])
            ->orderBy('kode_program')
            ->get();

        foreach ($programs as $prog) {
            $progLeafs = [];
            $kegRows = [];

            foreach ($prog->kegiatans as $keg) {
                $kegLeafs = [];
                $subRows = [];

                foreach ($keg->subKegiatans as $sub) {
                    $subLeafs = array_values($leafAccountsBySub[$sub->id] ?? []);
                    if (empty($subLeafs)) {
                        continue;
                    }

                    $kegLeafs = array_merge($kegLeafs, $subLeafs);
                    $subMetrics = $sumMetrics($subLeafs);

                    $currentSubRows = [
                        array_merge([
                            'kode' => $sub->kode_sub_kegiatan,
                            'uraian' => $sub->nama_sub_kegiatan,
                            'type' => 'sub_kegiatan',
                            'is_bold' => true,
                            'indent' => 2,
                        ], $subMetrics),
                    ];

                    $allPrefixes = [];
                    foreach ($subLeafs as $leaf) {
                        $parts = explode('.', $leaf['kode']);
                        for ($i = 2; $i <= count($parts); $i++) {
                            $prefix = implode('.', array_slice($parts, 0, $i));
                            $allPrefixes[$prefix] = true;
                        }
                    }
                    ksort($allPrefixes);

                    foreach (array_keys($allPrefixes) as $prefix) {
                        $prefixLeafs = array_filter($subLeafs, fn ($item) => str_starts_with($item['kode'], $prefix));
                        $isLeaf = count(explode('.', $prefix)) >= 6;
                        $pMetrics = $sumMetrics($prefixLeafs);

                        if ($isLeaf) {
                            $leafItem = $subLeafs[array_key_first(array_filter($subLeafs, fn ($item) => $item['kode'] === $prefix))] ?? null;
                            $currentSubRows[] = array_merge([
                                'kode' => $prefix,
                                'uraian' => $leafItem['uraian'] ?? ($standardTitles[$prefix] ?? $prefix),
                                'type' => 'rekening',
                                'is_bold' => false,
                                'indent' => 4,
                            ], $pMetrics);
                        } else {
                            $currentSubRows[] = array_merge([
                                'kode' => $prefix,
                                'uraian' => $standardTitles[$prefix] ?? $prefix,
                                'type' => 'akun_parent',
                                'is_bold' => true,
                                'indent' => 3,
                            ], $pMetrics);
                        }
                    }

                    $subRows = array_merge($subRows, $currentSubRows);
                }

                if (empty($kegLeafs)) {
                    continue;
                }

                $progLeafs = array_merge($progLeafs, $kegLeafs);
                $kegMetrics = $sumMetrics($kegLeafs);

                $kegRows[] = array_merge([
                    'kode' => $keg->kode_kegiatan,
                    'uraian' => $keg->nama_kegiatan,
                    'type' => 'kegiatan',
                    'is_bold' => true,
                    'indent' => 1,
                ], $kegMetrics);

                $kegRows = array_merge($kegRows, $subRows);
            }

            if (empty($progLeafs)) {
                continue;
            }

            $progMetrics = $sumMetrics($progLeafs);

            $rows[] = array_merge([
                'kode' => $prog->kode_program,
                'uraian' => $prog->nama_program,
                'type' => 'program',
                'is_bold' => true,
                'indent' => 0,
            ], $progMetrics);

            $rows = array_merge($rows, $kegRows);
        }

        // 6. Pajak & Potongan dari SPJ yang dicairkan (filter bidang, kegiatan, sub kegiatan)
        $spjsCair = PengajuanSpj::query()
            ->where('tahun_anggaran', $tahun)
            ->where('status', 'dicairkan')
            ->whereDate('tanggal_dicairkan', '<=', $akhirBulanIni)
            ->when($bidangId, fn ($query) => $query->where('bidang_id', $bidangId))
            ->when($kegiatanId, fn ($query) => $query->where('kegiatan_id', $kegiatanId))
            ->when($subKegiatanId, fn ($query) => $query->where('sub_kegiatan_id', $subKegiatanId))
            ->with(['masterToko', 'user'])
            ->get();

        $ppnTotal = (float) $spjsCair->sum('nilai_ppn');
        $pph22Total = (float) $spjsCair->sum('nilai_pph22');
        $totalPajak = (float) $spjsCair->sum('total_pajak');
        $nilaiBersihTotal = (float) $spjsCair->sum('nilai_bersih');

        $pajakItems = [];
        foreach ($spjsCair as $spj) {
            $pajakItems[] = [
                'id' => $spj->id,
                'nomor_pengajuan' => $spj->nomor_pengajuan,
                'tanggal_dicairkan' => $spj->tanggal_dicairkan ? Carbon::parse($spj->tanggal_dicairkan)->format('d/m/Y') : '-',
                'keperluan' => $spj->keperluan,
                'nama_toko' => $spj->nama_toko ?: ($spj->masterToko?->nama_toko ?? '-'),
                'total_belanja' => (float) $spj->total_belanja,
                'nilai_ppn' => (float) $spj->nilai_ppn,
                'nilai_pph22' => (float) $spj->nilai_pph22,
                'total_pajak' => (float) $spj->total_pajak,
                'nilai_bersih' => (float) $spj->nilai_bersih,
            ];
        }

        $totalSpjUp = $globalTotals['up_sd_ini'];
        $totalSpjLs = $globalTotals['ls_gaji_sd_ini'] + $globalTotals['ls_bj_sd_ini'];
        $totalPengeluaran = $globalTotals['total_spj'];

        return [
            'rows' => $rows,
            'summary' => $globalTotals,
            'penerimaan' => [
                'up' => ['lalu' => 0.0, 'ini' => 0.0, 'sd_ini' => 0.0],
                'gu' => ['lalu' => $globalTotals['up_lalu'], 'ini' => $globalTotals['up_ini'], 'sd_ini' => $globalTotals['up_sd_ini']],
                'tu' => ['lalu' => 0.0, 'ini' => 0.0, 'sd_ini' => 0.0],
                'ls' => ['lalu' => $globalTotals['ls_gaji_lalu'] + $globalTotals['ls_bj_lalu'], 'ini' => $globalTotals['ls_gaji_ini'] + $globalTotals['ls_bj_ini'], 'sd_ini' => $totalSpjLs],
                'total_bruto' => $totalPengeluaran + $totalPajak,
            ],
            'pajak' => [
                'ppn' => $ppnTotal,
                'pph22' => $pph22Total,
                'pph21' => 0.0,
                'pph23' => 0.0,
                'total_pajak' => $totalPajak,
                'nilai_bersih' => $nilaiBersihTotal,
                'items' => $pajakItems,
            ],
            'pengeluaran' => [
                'spj_up_gu' => $totalSpjUp,
                'spj_tu' => 0.0,
                'spj_ls' => $totalSpjLs,
                'pengembalian_sts' => 0.0,
                'total_pengeluaran' => $totalPengeluaran,
                'saldo_kas' => 0.0,
            ],
            'rekening_rows' => $rows,
        ];
    }
}
