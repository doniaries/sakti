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
    public static function import(string $pdfPath, bool $skipZeroItems = true, ?int $targetBidangId = null): array
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
        preg_match('/Program\s*:\s*([0-9\.]+)\s+(.+)/', $fullText, $progM);
        preg_match('/Kegiatan\s*:\s*([0-9\.]+)\s+(.+)/', $fullText, $kegM);
        preg_match('/Sub Kegiatan\s*:\s*([0-9\.]+)\s+(.+)/', $fullText, $subM);
        preg_match('/Sumber Pendanaan\s*:\s*(.+)/', $fullText, $sumberM);

        $tahun = (int) ($tahunM[1] ?? date('Y'));
        $programKode = trim($progM[1] ?? '');
        $programNama = trim($progM[2] ?? '');
        $kegiatanKode = trim($kegM[1] ?? '');
        $kegiatanNama = trim($kegM[2] ?? '');
        $subKegiatanKode = trim($subM[1] ?? '');
        $subKegiatanNama = trim($subM[2] ?? '');
        $sumberDana = trim($sumberM[1] ?? 'Dana Alokasi Umum (DAU)');

        // 2. Ekstrak Baris Rincian Belanja
        $lines = explode("\n", $fullText);
        $currentRek = null;
        $currentPaket = null;
        $rawItems = [];

        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);

            if (preg_match('/^(5\.1\.\d{2}\.\d{2}\.\d{3}\.\d{5})/', $line, $mRek)) {
                $currentRek = $mRek[1];
                continue;
            }

            if (preg_match('/^\[\s*#\s*\]\s*(.+)/', $line, $mPaket)) {
                $currentPaket = trim($mPaket[1]);
                continue;
            }

            if (str_contains($line, 'Spesifikasi :')) {
                $specText = $line;
                while (
                    $i + 1 < count($lines) &&
                    ! preg_match('/\d+\s*%\s*Rp\./', $specText) &&
                    ! preg_match('/^5\.1\./', trim($lines[$i + 1])) &&
                    ! preg_match('/^\[\s*#\s*\]/', trim($lines[$i + 1]))
                ) {
                    $i++;
                    $specText .= ' ' . trim($lines[$i]);
                }

                if (preg_match('/Spesifikasi\s*:\s*(.+?)\s+([\d\.,]+|-)\s+([A-Za-z\/\s]+?)\s+([\d\.,]+)\s+\d+\s*%\s+Rp\.\s*([\d\.,]+)/', $specText, $mItem)) {
                    $rawItems[] = [
                        'kode_rekening' => $currentRek,
                        'paket' => $currentPaket,
                        'nama_item' => trim($mItem[1]),
                        'volume' => static::cleanNum($mItem[2]),
                        'satuan' => trim($mItem[3]),
                        'harga_satuan' => static::cleanNum($mItem[4]),
                        'pagu_total' => static::cleanNum($mItem[5]),
                    ];
                }
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
            $targetBidangId
        ) {
            // 1. Program
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
            }

            // 2. Sub Kegiatan & Kegiatan
            $subKegiatan = MasterSubKegiatan::where('kode_sub_kegiatan', $subKegiatanKode)
                ->orWhere('kode_sub_kegiatan', 'like', '%' . $subKegiatanKode . '%')
                ->first();

            $kegiatan = $subKegiatan?->kegiatan;

            // Jika user memilih bidang secara eksplisit saat import
            $bidangId = $targetBidangId;

            // Jika kegiatan belum ada, buatkan master kegiatannya
            if (! $kegiatan && ! empty($kegiatanKode)) {
                $kegiatan = MasterKegiatan::firstOrCreate(
                    [
                        'tahun_anggaran' => $tahun,
                        'kode_kegiatan' => $kegiatanKode,
                    ],
                    [
                        'bidang_id' => $bidangId,
                        'program_id' => $program?->id,
                        'nama_kegiatan' => mb_strtoupper($kegiatanNama ?: 'KEGIATAN PARIWISATA', 'UTF-8'),
                        'pagu_anggaran' => array_sum(array_column($rawItems, 'pagu_total')),
                    ]
                );
            }

            // Jika sub kegiatan belum ada, buatkan master sub kegiatannya
            if (! $subKegiatan && ! empty($subKegiatanKode)) {
                $subKegiatan = MasterSubKegiatan::firstOrCreate(
                    [
                        'tahun_anggaran' => $tahun,
                        'kode_sub_kegiatan' => $subKegiatanKode,
                    ],
                    [
                        'kegiatan_id' => $kegiatan?->id,
                        'nama_sub_kegiatan' => $subKegiatanNama ?: 'Sub Kegiatan',
                    ]
                );
            }

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
                    Log::warning('SIPD RKA Importer: Kode rekening tidak ditemukan: ' . $item['kode_rekening']);
                    continue;
                }

                $satuanNama = trim((string) ($item['satuan'] ?? 'Unit'));
                if ($satuanNama !== '') {
                    Satuan::firstOrCreate(['nama' => $satuanNama]);
                }

                $namaItem = trim((string) ($item['nama_item'] ?? 'Item Belanja'));
                $hargaSatuan = (float) ($item['harga_satuan'] ?? 0);

                $masterBarang = MasterBarang::firstOrCreate(
                    [
                        'kode_rekening_id' => $rek->id,
                        'nama_barang' => $namaItem,
                    ],
                    [
                        'harga_satuan' => $hargaSatuan,
                    ]
                );

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
