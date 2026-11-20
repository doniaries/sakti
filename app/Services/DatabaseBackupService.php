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
     * Daftar tabel inti (wajib) yang menjadi fondasi sistem & referensi utama.
     *
     * @var array<int, string>
     */
    public const CORE_TABLES = [
        'users',
        'roles',
        'permissions',
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',
        'pengaturan_aplikasis',
        'instansis',
        'bidangs',
        'jabatans',
        'jadwal_kerjas',
        'jenis_izins',
        'hari_liburs',
        'menus',
        'menu_items',
        'menu_locations',
        'portal_links',
    ];

    /**
     * Ambil seluruh tabel aplikasi beserta jumlah barisnya, dikelompokkan menjadi Inti (Wajib) & Pendukung.
     *
     * @return array{core: array<string, string>, supporting: array<string, string>}
     */
    public static function listAllTablesGrouped(): array
    {
        $dbName = (string) config('database.connections.mysql.database', 'sakti');
        $skip = [
            'migrations',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
            'sessions',
            'password_reset_tokens',
            'personal_access_tokens',
        ];

        $rows = DB::select(
            'SELECT TABLE_NAME, TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME',
            [$dbName]
        );

        $core = [];
        $supporting = [];

        foreach ($rows as $row) {
            $name = (string) $row->TABLE_NAME;
            if (in_array($name, $skip, true)) {
                continue;
            }

            $count = (int) $row->TABLE_ROWS;
            $label = "{$name} ({$count} baris)";

            if (in_array($name, self::CORE_TABLES, true)) {
                $core[$name] = $label;
            } else {
                $supporting[$name] = $label;
            }
        }

        return [
            'core' => $core,
            'supporting' => $supporting,
        ];
    }

    /**
     * Ambil informasi relasi Foreign Key (induk & anak) untuk tabel yang dipilih.
     *
     * @param  array<int, string>  $selectedTables
     * @return array<int, string>
     */
    public static function getTableRelationWarnings(array $selectedTables): array
    {
        if ($selectedTables === []) {
            return [];
        }

        $dbName = (string) config('database.connections.mysql.database', 'sakti');
        $placeholders = implode(',', array_fill(0, count($selectedTables), '?'));

        $fks = DB::select(
            "SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL
               AND (TABLE_NAME IN ({$placeholders}) OR REFERENCED_TABLE_NAME IN ({$placeholders}))
             ORDER BY TABLE_NAME, REFERENCED_TABLE_NAME",
            array_merge([$dbName], $selectedTables, $selectedTables)
        );

        $messages = [];
        foreach ($fks as $fk) {
            $child = (string) $fk->TABLE_NAME;
            $parent = (string) $fk->REFERENCED_TABLE_NAME;
            $col = (string) $fk->COLUMN_NAME;

            if (in_array($child, $selectedTables, true) && ! in_array($parent, $selectedTables, true)) {
                $messages[] = "• <strong>{$child}</strong> ({$col}) bergantung pada tabel induk <strong>{$parent}</strong> — pastikan <strong>{$parent}</strong> tersedia saat restore.";
            } elseif (in_array($parent, $selectedTables, true) && ! in_array($child, $selectedTables, true)) {
                $messages[] = "• <strong>{$child}</strong> ({$col}) merujuk ke <strong>{$parent}</strong> — menghapus <strong>{$parent}</strong> dapat memengaruhi data di <strong>{$child}</strong>.";
            }
        }

        return array_values(array_unique($messages));
    }

    /**
     * @param  array<int, string>|null  $tables
     * @return array{filename: string, path: string, size: int}
     */
    public static function createBackup(?array $tables = null, string $prefix = 'backup'): array
    {
        $dir = self::backupDirectory();
        $dbName = (string) config('database.connections.mysql.database', 'sakti');
        $filename = $prefix . '-' . $dbName . '-' . now()->format('Y-m-d_His') . '.sql';
        $filePath = $dir . DIRECTORY_SEPARATOR . $filename;

        if (! self::dumpWithMysqldump($filePath, $tables)) {
            self::dumpWithPdo($filePath, $tables);
        }

        return [
            'filename' => $filename,
            'path' => $filePath,
            'size' => File::size($filePath),
        ];
    }

    /**
     * @param  array<int, string>  $tables
     */
    public static function truncateTables(array $tables): int
    {
        $existing = array_values(array_filter(
            $tables,
            fn (string $table): bool => \Illuminate\Support\Facades\Schema::hasTable($table)
        ));

        if ($existing === []) {
            return 0;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($existing as $table) {
                DB::table($table)->truncate();
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        return count($existing);
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

    /**
     * @param  array<int, string>|null  $tables
     */
    private static function dumpWithMysqldump(string $filePath, ?array $tables = null): bool
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

        if ($tables !== null && $tables !== []) {
            foreach ($tables as $table) {
                $args[] = $table;
            }
        }

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

    /**
     * @param  array<int, string>|null  $tables
     */
    private static function dumpWithPdo(string $filePath, ?array $tables = null): void
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

