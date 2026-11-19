<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BukuInventaris;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;

class BukuInventarisPdfImporter
{
    /**
     * Parse PDF Buku Inventaris Gabungan (BKAD) dan sinkronkan ke database.
     *
     * @return array{total_parsed: int, inserted: int, updated: int, total_harga: float}
     */
    public function import(string $pdfPath, string $mode = 'upsert'): array
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        $text = $pdf->getText();

        $rows = $this->parseRows($text);

        if (empty($rows)) {
            throw new \RuntimeException('Tidak ada baris data aset yang dapat dibaca dari file PDF. Pastikan format PDF sesuai standar Buku Inventaris Gabungan BKAD.');
        }

        $inserted = 0;
        $updated = 0;
        $totalHarga = 0.0;

        DB::transaction(function () use ($rows, $mode, &$inserted, &$updated, &$totalHarga): void {
            if ($mode === 'replace') {
                BukuInventaris::query()->delete();
            }

            foreach ($rows as $row) {
                $totalHarga += (float) $row['harga'];

                if ($mode === 'replace') {
                    BukuInventaris::create($row);
                    $inserted++;
                    continue;
                }

                $existing = BukuInventaris::query()
                    ->where('kode_barang', $row['kode_barang'])
                    ->where('register', $row['register'])
                    ->where('nama_barang', $row['nama_barang'])
                    ->where('tahun_pembelian', $row['tahun_pembelian'])
                    ->first();

                if ($existing) {
                    $existing->update($row);
                    $updated++;
                } else {
                    BukuInventaris::create($row);
                    $inserted++;
                }
            }
        });

        return [
            'total_parsed' => count($rows),
            'inserted' => $inserted,
            'updated' => $updated,
            'total_harga' => $totalHarga,
        ];
    }

    /**
     * Ekstrak baris-baris aset dari teks PDF BKAD.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function parseRows(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $rows = [];
        $currentBlock = '';

        // Pola awal baris data: bisa diawali nomor urut + spasi + kode barang (XX.XX.XX.XX.XXX)
        // atau kode barang dulu baru nomor urut di tengah (tergantung urutan ekstraksi PDF).
        $kodePattern = '/\b(\d{2}\.\d{2}\.\d{2}\.\d{2}\.\d{3})\b/';

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || $this->isHeaderOrFooterLine($trimmed)) {
                continue;
            }

            if (preg_match($kodePattern, $trimmed)) {
                if ($currentBlock !== '') {
                    $parsed = $this->parseBlock($currentBlock);
                    if ($parsed !== null) {
                        $rows[] = $parsed;
                    }
                }
                $currentBlock = $trimmed;
            } elseif ($currentBlock !== '') {
                $currentBlock .= ' ' . $trimmed;
            }
        }

        if ($currentBlock !== '') {
            $parsed = $this->parseBlock($currentBlock);
            if ($parsed !== null) {
                $rows[] = $parsed;
            }
        }

        return $rows;
    }

    protected function isHeaderOrFooterLine(string $line): bool
    {
        $ignorePrefixes = [
            'BUKU INVENTARIS GABUNGAN',
            'PEMERINTAH KABUPATEN',
            'Provinsi :',
            'Kab./Kota :',
            'Bidang :',
            'Unit Organisasi :',
            'Sub Unit Organisasi :',
            'U P B :',
            'NOMOR',
            'Kode Barang',
            'SPESIFIKASI BARANG',
            '1 2 3 4 5',
            'Total ',
            'MENGETAHUI',
            'Muaro Sijunjung,',
            'NIP.',
        ];

        foreach ($ignorePrefixes as $prefix) {
            if (str_starts_with($line, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse satu blok teks menjadi array kolom BukuInventaris.
     *
     * @return array<string, mixed>|null
     */
    protected function parseBlock(string $block): ?array
    {
        // Normalisasi spasi ganda
        $block = preg_replace('/\s+/', ' ', trim($block)) ?? $block;

        // Harus memiliki kode barang XX.XX.XX.XX.XXX dan harga format Indonesia (misal 200.256.000,00)
        if (! preg_match('/\b(\d{2}\.\d{2}\.\d{2}\.\d{2}\.\d{3})\b/', $block, $kodeMatch)) {
            return null;
        }
        $kodeBarang = $kodeMatch[1];

        // Cari harga: angka dengan titik ribuan dan koma desimal (contoh: 200.256.000,00)
        if (! preg_match('/(\d{1,3}(?:\.\d{3})*,\d{2})\s*(.*)$/', $block, $hargaMatch)) {
            return null;
        }

        $hargaRaw = $hargaMatch[1];
        $harga = (float) str_replace(['.', ','], ['', '.'], $hargaRaw);
        $keterangan = trim($hargaMatch[2]);

        // Ambil kondisi barang (Baik / Kurang Baik / Rusak Berat) + jumlah barang sebelum harga
        $keadaan = 'Baik';
        $jumlahBarang = 1;
        $beforeHarga = trim(substr($block, 0, (int) strpos($block, $hargaRaw)));

        if (preg_match('/\b(Baik|Kurang Baik|Rusak Berat)\s+(\d+)\s*$/i', $beforeHarga, $condMatch)) {
            $keadaan = ucwords(strtolower($condMatch[1]));
            $jumlahBarang = (int) $condMatch[2];
            $beforeHarga = trim(substr($beforeHarga, 0, -strlen($condMatch[0])));
        }

        // Ambil tahun pembelian (4 digit 19xx/20xx)
        $tahunPembelian = null;
        if (preg_match('/\b((?:19|20)\d{2})\b/', $beforeHarga, $yearMatch)) {
            $tahunPembelian = (int) $yearMatch[1];
        }

        // Ambil register (000001 atau 000001 s/d 000002)
        $register = '-';
        if (preg_match('/\b(\d{6}(?:\s*s\/d\s*\d{6})?)\b/', $beforeHarga, $regMatch)) {
            $register = preg_replace('/\s+/', ' ', $regMatch[1]) ?? $regMatch[1];
        }

        // Ambil nomor urut (angka 1-4 digit di awal atau sebelum/sesudah kode barang)
        $nomorUrut = null;
        if (preg_match('/^\s*(\d{1,4})\b/', $beforeHarga, $urutMatch)) {
            $nomorUrut = (int) $urutMatch[1];
        }

        // Ambil asal perolehan
        $asalPerolehan = 'Pembelian';
        if (preg_match('/\b(Pembelian|Hibah|Mutasi|Sumbangan)\b/i', $beforeHarga, $asalMatch)) {
            $asalPerolehan = ucfirst(strtolower($asalMatch[1]));
        }

        // Ambil nama barang: teks setelah register (dan sebelum merk/asal/tahun)
        $afterReg = $beforeHarga;
        if ($register !== '-' && str_contains($beforeHarga, $register)) {
            $afterReg = trim(substr($beforeHarga, strpos($beforeHarga, $register) + strlen($register)));
        } elseif (str_contains($beforeHarga, $kodeBarang)) {
            $afterReg = trim(substr($beforeHarga, strpos($beforeHarga, $kodeBarang) + strlen($kodeBarang)));
        }

        // Bersihkan nomor urut jika masih menempel di awal $afterReg
        $afterReg = preg_replace('/^\d{1,4}\s+/', '', $afterReg) ?? $afterReg;
        $namaBarang = trim(explode(' - ', $afterReg)[0]);
        if ($namaBarang === '') {
            $namaBarang = 'Aset ' . $kodeBarang;
        }
        // Batasi panjang nama_barang agar aman dengan kolom database
        $namaBarang = mb_substr($namaBarang, 0, 255);

        return [
            'nomor_urut' => $nomorUrut,
            'kode_barang' => $kodeBarang,
            'register' => $register,
            'nama_barang' => $namaBarang,
            'asal_perolehan' => $asalPerolehan,
            'tahun_pembelian' => $tahunPembelian,
            'keadaan_barang' => $keadaan,
            'jumlah_barang' => $jumlahBarang,
            'harga' => $harga,
            'keterangan' => $keterangan !== '' ? $keterangan : null,
        ];
    }
}
