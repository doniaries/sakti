<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    public static function backupDirectory(): string
    {
        $dir = storage_path('app/backups');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        return $dir;
    }

    /**
     * @return array{filename: string, path: string, size: int}
     */
    public static function createBackup(): array
    {
        $dir = self::backupDirectory();
        $dbName = (string) config('database.connections.mysql.database', 'sakti');
        $filename = 'backup-' . $dbName . '-' . now()->format('Y-m-d_His') . '.sql';
        $filePath = $dir . DIRECTORY_SEPARATOR . $filename;

        if (! self::dumpWithMysqldump($filePath)) {
            self::dumpWithPdo($filePath);
        }

        return [
            'filename' => $filename,
            'path' => $filePath,
            'size' => File::size($filePath),
        ];
    }

    /**
     * @return array<int, array{filename: string, path: string, size: int, size_human: string, created_at: string}>
     */
    public static function listBackups(): array
    {
        $dir = self::backupDirectory();
        $files = File::glob($dir . DIRECTORY_SEPARATOR . '*.sql') ?: [];

        usort($files, fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));

        return array_map(function (string $path): array {
            $bytes = (int) filesize($path);

            return [
                'filename' => basename($path),
                'path' => $path,
                'size' => $bytes,
                'size_human' => self::formatBytes($bytes),
                'created_at' => date('d/m/Y H:i:s', (int) filemtime($path)),
            ];
        }, $files);
    }

    public static function resolveBackupPath(string $filename): string
    {
        $safeName = basename($filename);
        if (! str_ends_with(strtolower($safeName), '.sql')) {
            throw new \InvalidArgumentException('Format berkas harus .sql');
        }

        $path = self::backupDirectory() . DIRECTORY_SEPARATOR . $safeName;
        if (! File::exists($path)) {
            throw new \RuntimeException('Berkas backup tidak ditemukan.');
        }

        return $path;
    }

    public static function deleteBackup(string $filename): void
    {
        $path = self::resolveBackupPath($filename);
        File::delete($path);
    }

    public static function restoreFromFile(string $filePath): void
    {
        if (! File::exists($filePath)) {
            throw new \RuntimeException('Berkas SQL tidak ditemukan.');
        }

        if (self::restoreWithMysqlCli($filePath)) {
            return;
        }

        $sql = File::get($filePath);
        if (trim($sql) === '') {
            throw new \RuntimeException('Berkas SQL kosong.');
        }

        DB::unprepared("SET FOREIGN_KEY_CHECKS=0;\n" . $sql . "\nSET FOREIGN_KEY_CHECKS=1;");
    }

    private static function dumpWithMysqldump(string $filePath): bool
    {
        $binary = self::findBinary('mysqldump');
        if ($binary === null) {
            return false;
        }

        $cfg = config('database.connections.mysql');
        $args = [
            $binary,
            '--host=' . ($cfg['host'] ?? '127.0.0.1'),
            '--port=' . ($cfg['port'] ?? 3306),
            '--user=' . ($cfg['username'] ?? 'root'),
            '--single-transaction',
            '--routines',
            '--triggers',
            '--hex-blob',
            '--result-file=' . $filePath,
            (string) ($cfg['database'] ?? 'sakti'),
        ];

        $env = [];
        if (filled($cfg['password'] ?? null)) {
            $env['MYSQL_PWD'] = (string) $cfg['password'];
        }

        $process = new Process($args, base_path(), $env, null, 300);
        $process->run();

        return $process->isSuccessful() && File::exists($filePath) && File::size($filePath) > 0;
    }

    private static function restoreWithMysqlCli(string $filePath): bool
    {
        $binary = self::findBinary('mysql');
        if ($binary === null) {
            return false;
        }

        $cfg = config('database.connections.mysql');
        $args = [
            $binary,
            '--host=' . ($cfg['host'] ?? '127.0.0.1'),
            '--port=' . ($cfg['port'] ?? 3306),
            '--user=' . ($cfg['username'] ?? 'root'),
            '--default-character-set=utf8mb4',
            (string) ($cfg['database'] ?? 'sakti'),
        ];

        $env = [];
        if (filled($cfg['password'] ?? null)) {
            $env['MYSQL_PWD'] = (string) $cfg['password'];
        }

        $stream = fopen($filePath, 'rb');
        if ($stream === false) {
            return false;
        }

        try {
            $process = new Process($args, base_path(), $env, $stream, 300);
            $process->run();

            return $process->isSuccessful();
        } finally {
            fclose($stream);
        }
    }

    private static function dumpWithPdo(string $filePath): void
    {
        /** @var PDO $pdo */
        $pdo = DB::connection('mysql')->getPdo();
        $dbName = (string) config('database.connections.mysql.database', 'sakti');

        $handle = fopen($filePath, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('Tidak dapat membuat berkas backup SQL.');
        }

        try {
            fwrite($handle, "-- SAKTI SQL Backup\n");
            fwrite($handle, "-- Database: `{$dbName}`\n");
            fwrite($handle, "-- Generated: " . now()->toDateTimeString() . "\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

            $tables = $pdo->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"')->fetchAll(PDO::FETCH_NUM);

            foreach ($tables as $tableRow) {
                $table = (string) $tableRow[0];
                $createRow = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
                if (! $createRow || empty($createRow[1])) {
                    continue;
                }

                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
                fwrite($handle, $createRow[1] . ";\n\n");

                $stmt = $pdo->query("SELECT * FROM `{$table}`");
                $batch = [];
                $columnList = null;

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if ($columnList === null) {
                        $cols = array_map(fn (string $c): string => "`{$c}`", array_keys($row));
                        $columnList = '(' . implode(', ', $cols) . ')';
                    }

                    $values = array_map(function (mixed $val) use ($pdo): string {
                        if ($val === null) {
                            return 'NULL';
                        }
                        if (is_int($val) || is_float($val)) {
                            return (string) $val;
                        }

                        return $pdo->quote((string) $val);
                    }, array_values($row));

                    $batch[] = '(' . implode(', ', $values) . ')';

                    if (count($batch) >= 200) {
                        fwrite($handle, "INSERT INTO `{$table}` {$columnList} VALUES\n" . implode(",\n", $batch) . ";\n");
                        $batch = [];
                    }
                }

                if ($batch !== []) {
                    fwrite($handle, "INSERT INTO `{$table}` {$columnList} VALUES\n" . implode(",\n", $batch) . ";\n");
                }

                fwrite($handle, "\n");
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            fclose($handle);
        }
    }

    private static function findBinary(string $name): ?string
    {
        $candidates = [
            $name,
            ...glob('C:\\laragon\\bin\\mysql\\*\\bin\\' . $name . '.exe') ?: [],
            '/usr/bin/' . $name,
            '/usr/local/bin/' . $name,
        ];

        foreach ($candidates as $candidate) {
            if ($candidate !== $name && is_file($candidate)) {
                return $candidate;
            }
        }

        $which = PHP_OS_FAMILY === 'Windows' ? "where.exe {$name}" : "command -v {$name}";
        $probe = Process::fromShellCommandline($which);
        $probe->run();
        if ($probe->isSuccessful()) {
            $first = trim(explode("\n", $probe->getOutput())[0] ?? '');
            if ($first !== '') {
                return $first;
            }
        }

        return null;
    }

    private static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        }

        return $bytes . ' B';
    }
}

