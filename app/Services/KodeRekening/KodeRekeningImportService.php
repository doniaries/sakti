<?php

namespace App\Services\KodeRekening;

use App\Models\KodeRekening;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KodeRekeningImportService
{
    /**
     * Import kode rekening dari file CSV atau SQL
     */
    public function import(string $filePath, string $mode = 'update_or_create'): array
    {
        $realPath = Storage::disk('local')->path($filePath);
        if (! file_exists($realPath)) {
            $realPath = $filePath;
        }

        if (! file_exists($realPath)) {
            throw new \Exception("File import tidak ditemukan di lokasi: {$realPath}");
        }

        $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));

        if ($mode === 'replace_all') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            KodeRekening::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        if ($extension === 'sql') {
            return $this->importSql($realPath);
        }

        return $this->importCsv($realPath);
    }

    protected function importCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new \Exception('Gagal membuka file CSV.');
        }

        $header = fgetcsv($handle, 4096, ',');
        if (! $header) {
            fclose($handle);
            throw new \Exception('File CSV kosong.');
        }

        // Normalisasi header
        $headerMap = [];
        foreach ($header as $idx => $col) {
            $colClean = strtolower(trim($col));
            if (in_array($colClean, ['kode_lengkap', 'kode', 'kode_rekening'])) {
                $headerMap['kode'] = $idx;
            } elseif (in_array($colClean, ['uraian', 'uraian_rekening', 'nama', 'nama_rekening'])) {
                $headerMap['uraian'] = $idx;
            }
        }

        if (! isset($headerMap['kode']) || ! isset($headerMap['uraian'])) {
            $headerMap['kode'] = 0;
            $headerMap['uraian'] = 1;
        }

        $count = 0;
        $batch = [];

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 4096, ',')) !== false) {
                if (empty($row) || ! isset($row[$headerMap['kode']])) {
                    continue;
                }

                $kode = trim($row[$headerMap['kode']]);
                $uraian = trim($row[$headerMap['uraian']] ?? '');

                if (empty($kode) || empty($uraian)) {
                    continue;
                }

                $parts = explode('.', $kode);
                $batch[] = [
                    'kode_lengkap' => $kode,
                    'uraian' => mb_strtoupper($uraian, 'UTF-8'),
                    'akun' => $parts[0] ?? '5',
                    'kelompok' => $parts[1] ?? '1',
                    'jenis' => $parts[2] ?? '02',
                    'objek' => $parts[3] ?? '01',
                    'rincian_objek' => $parts[4] ?? '001',
                    'sub_rincian_objek' => $parts[5] ?? '00001',
                    'sub_sub_rincian_objek' => $parts[6] ?? '000',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) >= 500) {
                    KodeRekening::upsert(
                        $batch,
                        ['kode_lengkap'],
                        ['uraian', 'akun', 'kelompok', 'jenis', 'objek', 'rincian_objek', 'sub_rincian_objek', 'sub_sub_rincian_objek', 'updated_at']
                    );
                    $count += count($batch);
                    $batch = [];
                }
            }

            if (! empty($batch)) {
                KodeRekening::upsert(
                    $batch,
                    ['kode_lengkap'],
                    ['uraian', 'akun', 'kelompok', 'jenis', 'objek', 'rincian_objek', 'sub_rincian_objek', 'sub_sub_rincian_objek', 'updated_at']
                );
                $count += count($batch);
            }

            DB::commit();
            fclose($handle);

            return [
                'type' => 'csv',
                'imported' => $count,
                'total' => KodeRekening::count(),
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }
    }

    protected function importSql(string $path): array
    {
        $sql = file_get_contents($path);
        if (empty($sql)) {
            throw new \Exception('File SQL kosong.');
        }

        DB::beginTransaction();
        try {
            DB::unprepared($sql);
            DB::commit();

            return [
                'type' => 'sql',
                'imported' => null,
                'total' => KodeRekening::count(),
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

