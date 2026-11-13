<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\DatabaseBackupService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
                        ->acceptedFileTypes(['.sql', 'application/sql', 'text/plain', 'application/octet-stream'])
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

