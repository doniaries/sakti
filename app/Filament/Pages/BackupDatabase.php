<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\DatabaseBackupService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use UnitEnum;

class BackupDatabase extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Backup & Restore SQL';

    protected static ?string $title = 'Backup & Restore Database SQL';

    protected static ?int $navigationSort = 97;

    protected string $view = 'filament.pages.backup-database';

    /** @var array<int, array{filename: string, path: string, size: int, size_human: string, created_at: string}> */
    public array $backups = [];

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user !== null && $user->hasAnyRole(['super_admin', 'admin']);
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->refreshBackups();
    }

    public function refreshBackups(): void
    {
        $this->backups = DatabaseBackupService::listBackups();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_backup')
                ->label('Buat Backup SQL')
                ->icon('heroicon-o-circle-stack')
                ->color('primary')
                ->authorize(fn (): bool => static::canAccess())
                ->action(function (): void {
                    $result = DatabaseBackupService::createBackup();
                    $this->refreshBackups();

                    Notification::make()
                        ->title('Backup SQL Berhasil Dibuat')
                        ->body("Berkas: {$result['filename']}")
                        ->success()
                        ->send();
                }),

            Action::make('download_fresh')
                ->label('Backup & Unduh Langsung')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->authorize(fn (): bool => static::canAccess())
                ->action(function (): BinaryFileResponse {
                    $result = DatabaseBackupService::createBackup();
                    $this->refreshBackups();

                    return response()->download($result['path'], $result['filename']);
                }),

            Action::make('backup_and_purge')
                ->label('Backup & Hapus Data Terpilih')
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->authorize(fn (): bool => static::canAccess())
                ->requiresConfirmation()
                ->modalWidth(Width::FourExtraLarge)
                ->modalHeading('Pilih Tabel untuk Backup & Hapus Data')
                ->modalDescription('Pilih tabel yang ingin di-backup terlebih dahulu, lalu dikosongkan. Periksa informasi relasi tabel di bawah agar proses restore nanti tidak error.')
                ->modalSubmitActionLabel('Backup & Hapus Sekarang')
                ->form(function (): array {
                    $grouped = DatabaseBackupService::listAllTablesGrouped();

                    return [
                        CheckboxList::make('core_tables')
                            ->label('Tabel Inti (Wajib / Fondasi Sistem)')
                            ->helperText('Hati-hati: mengosongkan tabel inti akan menghapus user, role, atau master sistem.')
                            ->options($grouped['core'])
                            ->columns(2)
                            ->bulkToggleable()
                            ->live(),

                        CheckboxList::make('supporting_tables')
                            ->label('Tabel Pendukung & Transaksi')
                            ->options($grouped['supporting'])
                            ->columns(2)
                            ->bulkToggleable()
                            ->live(),

                        Placeholder::make('relations_info')
                            ->label('Informasi Hubungan / Relasi Tabel Terpilih')
                            ->content(function (Get $get): HtmlString {
                                $selected = array_values(array_unique(array_merge(
                                    $get('core_tables') ?? [],
                                    $get('supporting_tables') ?? []
                                )));

                                if ($selected === []) {
                                    return new HtmlString('<span class="text-xs text-gray-500">Pilih satu atau lebih tabel di atas untuk melihat relasi Foreign Key-nya.</span>');
                                }

                                $warnings = DatabaseBackupService::getTableRelationWarnings($selected);
                                if ($warnings === []) {
                                    return new HtmlString('<span class="text-xs text-success-600 font-medium">Aman: seluruh tabel relasi yang terhubung sudah tercakup atau tidak ada ketergantungan eksternal.</span>');
                                }

                                return new HtmlString(
                                    '<div class="text-xs text-warning-700 dark:text-warning-300 space-y-1 bg-warning-50 dark:bg-warning-950/30 p-3 rounded-lg border border-warning-200 dark:border-warning-800">'
                                    . implode('<br>', $warnings)
                                    . '</div>'
                                );
                            }),
                    ];
                })
                ->action(function (array $data): void {
                    $tables = array_values(array_unique(array_merge(
                        $data['core_tables'] ?? [],
                        $data['supporting_tables'] ?? []
                    )));

                    if ($tables === []) {
                        Notification::make()
                            ->title('Tidak ada tabel yang dipilih')
                            ->warning()
                            ->send();

                        return;
                    }

                    $backup = DatabaseBackupService::createBackup($tables, 'backup-sebelum-hapus');
                    $purgedCount = DatabaseBackupService::truncateTables($tables);
                    $this->refreshBackups();

                    Notification::make()
                        ->title('Backup & Penghapusan Data Berhasil')
                        ->body("{$purgedCount} tabel dikosongkan. Backup aman tersimpan di: {$backup['filename']}")
                        ->success()
                        ->send();
                }),

            Action::make('restore_upload')
                ->label('Restore dari Berkas SQL')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('danger')
                ->authorize(fn (): bool => static::canAccess())
                ->requiresConfirmation()
                ->modalHeading('Restore Database dari Berkas SQL')
                ->modalDescription('PERINGATAN: Seluruh data saat ini akan ditimpa dengan isi berkas SQL yang diunggah. Pastikan Anda sudah membuat backup terbaru terlebih dahulu.')
                ->modalSubmitActionLabel('Ya, Restore Sekarang')
                ->form([
                    FileUpload::make('sql_file')
                        ->label('Berkas Backup (.sql)')
                        ->disk('local')
                        ->directory('backups/uploads')
                        ->rules(['file', 'extensions:sql'])
                        ->maxSize(102400)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $path = Storage::disk('local')->path($data['sql_file']);

                    try {
                        DatabaseBackupService::restoreFromFile($path);
                        Notification::make()
                            ->title('Restore Database Berhasil')
                            ->body('Seluruh data berhasil dikembalikan dari berkas SQL.')
                            ->success()
                            ->send();
                    } finally {
                        if (is_file($path)) {
                            @unlink($path);
                        }
                    }

                    $this->refreshBackups();
                }),
        ];
    }

    public function downloadFile(string $filename): BinaryFileResponse
    {
        abort_unless(static::canAccess(), 403);

        return response()->download(
            DatabaseBackupService::resolveBackupPath($filename),
            basename($filename)
        );
    }

    public function restoreFile(string $filename): void
    {
        abort_unless(static::canAccess(), 403);

        DatabaseBackupService::restoreFromFile(DatabaseBackupService::resolveBackupPath($filename));

        Notification::make()
            ->title('Restore Database Berhasil')
            ->body("Database dikembalikan dari {$filename}.")
            ->success()
            ->send();
    }

    public function deleteFile(string $filename): void
    {
        abort_unless(static::canAccess(), 403);

        DatabaseBackupService::deleteBackup($filename);
        $this->refreshBackups();

        Notification::make()
            ->title('Backup Dihapus')
            ->body($filename)
            ->warning()
            ->send();
    }
}

