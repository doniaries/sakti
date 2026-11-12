<?php

namespace App\Services\Rka;

use App\Models\Bidang;
use App\Models\KodeRekening;
use App\Models\MasterBarang;
use App\Models\MasterKegiatan;
use App\Models\MasterProgram;
use App\Models\MasterSubKegiatan;
use App\Models\RincianAnggaranBelanja;
use App\Models\Satuan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class SipdRkaImporterService
{
    /**
     * Parse and import RKA PDF into database natively in pure PHP.
     *
     * @param string $pdfPath Absolute path to PDF file
     * @param bool $skipZeroItems Skip items with 0 total budget
     * @return array Summary of import operation
     */
    public static function import(string $pdfPath, bool $skipZeroItems = true, ?int $targetBidangId = null, bool $allowOverwrite = false, ?int $targetJfId = null): array
    {
        if (! file_exists($pdfPath)) {
            throw new \RuntimeException("Berkas PDF tidak ditemukan: {$pdfPath}");
        }

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);
            $fullText = $pdf->getText();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Gagal membaca struktur PDF: ' . $e->getMessage());
        }

        if (empty(trim($fullText))) {
            throw new \RuntimeException('Dokumen PDF kosong atau tidak dapat diekstraksi teksnya.');
        }

        // 1. Ekstrak Metadata DPA
        preg_match('/Tahun Anggaran\s+(\d{4})/', $fullText, $tahunM);
        preg_match('/Program\s*:\s*([0-9\.]+)\s+([^\r\n]+)/', $fullText, $progM);
        preg_match('/Kegiatan\s*:\s*([0-9\.]+)\s+([^\r\n]+)/', $fullText, $kegM);
        preg_match('/Sub Kegiatan\s*:\s*([0-9\.]+)\s+([^\r\n]+)/', $fullText, $subM);
        preg_match('/Sumber Pendanaan\s*:\s*([^\r\n]+)/', $fullText, $sumberM);

        $tahun = (int) ($tahunM[1] ?? \App\Models\PengaturanAplikasi::getTahunAnggaranAktif());
        $programKode = trim($progM[1] ?? '');
        $programNama = ltrim(trim(preg_replace('/\s+/u', ' ', $progM[2] ?? '')), "- \t\n\r\0\x0B");
        $kegiatanKode = trim($kegM[1] ?? '');
        $kegiatanNama = ltrim(trim(preg_replace('/\s+/u', ' ', $kegM[2] ?? '')), "- \t\n\r\0\x0B");
        $subKegiatanKode = trim($subM[1] ?? '');
        $subKegiatanNama = ltrim(trim(preg_replace('/\s+/u', ' ', $subM[2] ?? '')), "- \t\n\r\0\x0B");
        $sumberDana = trim(preg_replace('/\s+/u', ' ', $sumberM[1] ?? 'Dana Alokasi Umum (DAU)'));

        // 2. Deteksi apakah RKA Sub Kegiatan ini sudah pernah diimpor di tahun anggaran tersebut
        if (! $allowOverwrite && ! empty($subKegiatanKode)) {
            $existingSub = MasterSubKegiatan::where('tahun_anggaran', $tahun)
                ->where(function ($q) use ($subKegiatanKode) {
                    $q->where('kode_sub_kegiatan', $subKegiatanKode)
                        ->orWhere('kode_sub_kegiatan', 'like', '%' . $subKegiatanKode . '%');
                })
                ->first();

            if ($existingSub) {
                $existingRkaCount = RincianAnggaranBelanja::where('tahun_anggaran', $tahun)
                    ->where('sub_kegiatan_id', $existingSub->id)
                    ->count();

                if ($existingRkaCount > 0) {
                    $lastUpdated = RincianAnggaranBelanja::where('tahun_anggaran', $tahun)
                        ->where('sub_kegiatan_id', $existingSub->id)
                        ->latest('updated_at')
                        ->value('updated_at');

                    $tglFormatted = $lastUpdated ? \Carbon\Carbon::parse($lastUpdated)->translatedFormat('d F Y H:i') : null;
                    $waktuInfo = $tglFormatted ? " pada {$tglFormatted} WIB" : '';

                    throw new \DomainException("Dokumen RKA untuk Sub Kegiatan [{$subKegiatanKode}] {$existingSub->nama_sub_kegiatan} (TA {$tahun}) sudah pernah diunggah sebelumnya ({$existingRkaCount} item belanja{$waktuInfo}). Sistem mencegah pengunggahan berulang. Aktifkan opsi 'Perbarui jika sudah pernah diimpor' apabila Anda ingin merevisi data ini.");
                }
            }
        }

        // 2. Ekstrak Baris Rincian Belanja
        $lines = explode("\n", $fullText);
        $currentRek = null;
        $currentPaket = null;
        $rawItems = [];

        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);

            if (preg_match('/(5\.1\.\d{2}\.\d{2}\.\d{2,3}\.\d{4,5})/', $line, $mRek)) {
                $currentRek = $mRek[1];
            }

            if (preg_match('/^\[\s*#\s*\]\s*(.+)/', $line, $mPaket)) {
                $currentPaket = trim($mPaket[1]);
            }

            if (preg_match('/Spesifikasi\s*:\s*(.+)/i', $line, $mSpec)) {
                $specText = trim($mSpec[1]);
                $detailLines = [];
                $j = $i + 1;
                while ($j < count($lines) && ! preg_match('/Spesifikasi\s*:/i', $lines[$j]) && ! preg_match('/^(?:5\.1\.|\[\s*#\s*\]|Kode Rekening)/', trim($lines[$j]))) {
                    $trimmed = trim($lines[$j]);
                    if (! empty($trimmed)) {
                        $detailLines[] = $trimmed;
                    }
                    $j++;
                }
                $i = $j - 1;

                $combinedDetail = implode(' ', $detailLines);
                preg_match_all('/(?:Rp\.?|Rp\s*)?([\d\.]+,\d{2})/', $combinedDetail, $mMoney);
                $moneyValues = array_map([static::class, 'cleanNum'], $mMoney[1] ?? []);

                $nonZero = array_values(array_filter($moneyValues, fn ($v) => $v > 0));
                $harga = 0.0;
                $pagu = 0.0;
                if (count($nonZero) >= 2) {
                    if (count($nonZero) >= 4) {
                        $harga = $nonZero[count($nonZero) - 2];
                        $pagu = $nonZero[count($nonZero) - 1];
                    } else {
                        $harga = $nonZero[0];
                        $pagu = $nonZero[1];
                    }
                } elseif (count($nonZero) === 1) {
                    $pagu = $nonZero[0];
                    $harga = $pagu;
                }

                $vol = 1.0;
                $satuan = 'Paket';
                if ($harga > 0 && $pagu > 0) {
                    $vol = round($pagu / $harga, 2);
                }

                if (preg_match('/(?:Kaleng|Kg|Liter|Meter|Rim|Pak|Pack|Lembar|Paket|Orang|Bulan|Hari|Pekerjaan|M2)/i', $combinedDetail, $mSat)) {
                    $satuan = trim($mSat[0]);
                }

                $rawItems[] = [
                    'kode_rekening' => $currentRek ?: '5.1.02.01.001.00024',
                    'paket' => $currentPaket,
                    'nama_item' => $specText,
                    'volume' => $vol,
                    'satuan' => $satuan,
                    'harga_satuan' => $harga,
                    'pagu_total' => $pagu,
                ];
            }
        }

        return DB::transaction(function () use (
            $tahun,
            $programKode,
            $programNama,
            $kegiatanKode,
            $kegiatanNama,
            $subKegiatanKode,
            $subKegiatanNama,
            $sumberDana,
            $rawItems,
            $skipZeroItems,
            $targetBidangId,
            $targetJfId
        ) {
            // 1. Program (firstOrCreate berdasarkan tahun_anggaran & kode_program: jika sudah ada dari RKA lain, TIDAK DIBUAT ULANG)
            $program = null;
            if (! empty($programKode)) {
                $program = MasterProgram::firstOrCreate(
                    [
                        'tahun_anggaran' => $tahun,
                        'kode_program' => $programKode,
                    ],
                    [
                        'nama_program' => mb_strtoupper($programNama ?: 'PROGRAM PARIWISATA', 'UTF-8'),
                    ]
                );

                if ($programNama && empty($program->nama_program)) {
                    $program->update(['nama_program' => mb_strtoupper($programNama, 'UTF-8')]);
                }
            }

            // 2. Kegiatan (firstOrCreate berdasarkan tahun_anggaran & kode_kegiatan: jika sudah ada dari RKA lain, TIDAK DIBUAT ULANG)
            $kegiatan = null;
            if (! empty($kegiatanKode)) {
                $kegiatan = MasterKegiatan::firstOrCreate(
                    [
                        'tahun_anggaran' => $tahun,
                        'kode_kegiatan' => $kegiatanKode,
                    ],
                    [
                        'program_id' => $program?->id,
                        'bidang_id' => $targetBidangId,
                        'nama_kegiatan' => mb_strtoupper($kegiatanNama ?: 'KEGIATAN PARIWISATA', 'UTF-8'),
                        'pagu_anggaran' => 0,
                    ]
                );

                // Hubungkan ke program jika sebelumnya program_id masih kosong
                if ($program && ! $kegiatan->program_id) {
                    $kegiatan->update(['program_id' => $program->id]);
                }

                // Jika user menentukan target bidang, selalu update kegiatan
                if ($targetBidangId) {
                    $kegiatan->update(['bidang_id' => $targetBidangId]);
                }
            }

            // 3. Sub Kegiatan (firstOrCreate berdasarkan tahun_anggaran & kode_sub_kegiatan: jika sudah ada, TIDAK DIBUAT ULANG)
            $subKegiatan = null;
            if (! empty($subKegiatanKode)) {
                $subKegiatan = MasterSubKegiatan::firstOrCreate(
                    [
                        'tahun_anggaran' => $tahun,
                        'kode_sub_kegiatan' => $subKegiatanKode,
                    ],
                    [
                        'kegiatan_id' => $kegiatan?->id,
                        'nama_sub_kegiatan' => $subKegiatanNama ?: 'Sub Kegiatan',
                        'jf_id' => $targetJfId,
                    ]
                );

                // Hubungkan ke kegiatan jika sebelumnya kegiatan_id belum sesuai
                if ($kegiatan && (! $subKegiatan->kegiatan_id || $subKegiatan->kegiatan_id !== $kegiatan->id)) {
                    $subKegiatan->update(['kegiatan_id' => $kegiatan->id]);
                }

                // Update JF jika ditentukan
                if ($targetJfId) {
                    $subKegiatan->update(['jf_id' => $targetJfId]);
                }
            }

            // Bidang RKA
            $bidangId = $targetBidangId;

            $importedItems = 0;
            $totalPaguImported = 0.0;
            $skippedItems = 0;

            foreach ($rawItems as $item) {
                $paguTotal = (float) ($item['pagu_total'] ?? 0);
                $vol = (float) ($item['volume'] ?? 0);

                if ($skipZeroItems && $paguTotal <= 0) {
                    $skippedItems++;
                    continue;
                }

                $rek = KodeRekening::where('kode_lengkap', $item['kode_rekening'])->first()
                    ?? KodeRekening::where('kode_lengkap', 'like', '%' . $item['kode_rekening'] . '%')->first();

                if (! $rek) {
                    $rek = KodeRekening::firstOrCreate(
                        ['kode_lengkap' => $item['kode_rekening']],
                        [
                            'akun' => '5',
                            'kelompok' => '1',
                            'jenis' => '02',
                            'objek' => '01',
                            'rincian_objek' => '001',
                            'sub_rincian_objek' => '00001',
                            'sub_sub_rincian_objek' => '000',
                            'uraian' => $item['nama_item'] ?? 'Belanja Operasional',
                        ]
                    );
                }

                $satuanNama = trim((string) ($item['satuan'] ?? 'Unit'));
                $satuanModel = null;
                if ($satuanNama !== '') {
                    $satuanModel = Satuan::firstOrCreate(['nama' => $satuanNama]);
                }

                $namaItem = trim((string) ($item['nama_item'] ?? 'Item Belanja'));
                $hargaSatuan = (float) ($item['harga_satuan'] ?? 0);

                $masterBarang = MasterBarang::firstOrCreate(
                    [
                        'kode_rekening_id' => $rek->id,
                        'nama_barang' => $namaItem,
                    ],
                    [
                        'satuan_id' => $satuanModel?->id,
                        'harga_satuan' => $hargaSatuan,
                    ]
                );

                if (! $masterBarang->satuan_id && $satuanModel) {
                    $masterBarang->update(['satuan_id' => $satuanModel->id]);
                }

                $existingRka = RincianAnggaranBelanja::where('tahun_anggaran', $tahun)
                    ->where('sub_kegiatan_id', $subKegiatan->id)
                    ->where('kode_rekening_id', $rek->id)
                    ->where('nama_item', $namaItem)
                    ->first();

                $finalBidangId = $bidangId ?? $existingRka?->bidang_id;

                RincianAnggaranBelanja::updateOrCreate(
                    [
                        'tahun_anggaran' => $tahun,
                        'sub_kegiatan_id' => $subKegiatan->id,
                        'kode_rekening_id' => $rek->id,
                        'nama_item' => $namaItem,
                    ],
                    [
                        'bidang_id' => $finalBidangId, // Null jika baru & belum ditugaskan
                        'master_barang_id' => $masterBarang->id,
                        'sumber_dana' => $sumberDana,
                        'volume' => max(1, (int) round($vol)),
                        'satuan' => $satuanNama ?: 'Unit',
                        'harga_satuan' => $hargaSatuan,
                        'pagu_total' => $paguTotal,
                        'keterangan' => $item['paket'] ? ('Paket: ' . $item['paket']) : null,
                    ]
                );

                $importedItems++;
                $totalPaguImported += $paguTotal;
            }

            // Sinkronkan pagu anggaran pada kegiatan berdasarkan total seluruh RKA di bawah sub kegiatan terkait
            if ($kegiatan) {
                $subKegiatanIds = MasterSubKegiatan::where('kegiatan_id', $kegiatan->id)->pluck('id');
                $actualPaguKegiatan = (float) RincianAnggaranBelanja::whereIn('sub_kegiatan_id', $subKegiatanIds)->sum('pagu_total');
                $kegiatan->update(['pagu_anggaran' => $actualPaguKegiatan]);
            }

            return [
                'success' => true,
                'sub_kegiatan' => $subKegiatan->nama_sub_kegiatan,
                'kode_sub_kegiatan' => $subKegiatan->kode_sub_kegiatan,
                'imported_items' => $importedItems,
                'skipped_items' => $skippedItems,
                'total_pagu' => $totalPaguImported,
            ];
        });
    }

    private static function cleanNum(mixed $val): float
    {
        if (! $val) {
            return 0.0;
        }
        $str = str_replace(['Rp.', 'Rp', ' '], '', trim((string) $val));
        $str = str_replace('.', '', $str);
        $str = str_replace(',', '.', $str);
        return (float) $str;
    }
}
