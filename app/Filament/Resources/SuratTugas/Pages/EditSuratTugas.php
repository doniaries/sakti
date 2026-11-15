<?php

declare(strict_types=1);

namespace App\Filament\Resources\SuratTugas\Pages;

use App\Filament\Resources\SuratTugas\SuratTugasResource;
use App\Models\SuratTugas;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSuratTugas extends EditRecord
{
    protected static string $resource = SuratTugasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload_tte')
                ->label(fn (SuratTugas $record): string => filled($record->file_tte) ? 'Update Nomor & TTE' : 'Input Nomor & Upload TTE')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->visible(fn (): bool => Auth::user()?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian', 'pa', 'sekretaris', 'kabid']) ?? false)
                ->modalHeading('Input Nomor Surat & Upload PDF TTE SRIKANDI')
                ->modalDescription('Masukkan nomor surat resmi dan file PDF hasil TTE dari aplikasi SRIKANDI (keduanya wajib diisi).')
                ->modalWidth('md')
                ->fillForm(fn (SuratTugas $record): array => [
                    'nomor_surat' => $record->nomor_surat,
                ])
                ->schema([
                    TextInput::make('nomor_surat')
                        ->label('Nomor Surat Resmi SRIKANDI')
                        ->placeholder('Contoh: 094/012/ST-DISPARPORA/2026')
                        ->required()
                        ->unique('surat_tugas', 'nomor_surat', ignoreRecord: true)
                        ->maxLength(100),

                    FileUpload::make('file_tte')
                        ->label('File PDF TTE SRIKANDI')
                        ->acceptedFileTypes(['application/pdf'])
                        ->disk('public')
                        ->directory('surat_tugas/tte')
                        ->maxSize(5120)
                        ->required(),
                ])
                ->action(function (SuratTugas $record, array $data): void {
                    $record->update([
                        'nomor_surat' => $data['nomor_surat'],
                        'file_tte' => $data['file_tte'],
                        'status' => 'terbit',
                    ]);

                    $record->kirimNotifikasiKeTim();

                    Notification::make()
                        ->title('PDF TTE SRIKANDI Berhasil Diunggah')
                        ->body('Surat Tugas resmi telah diterbitkan dan dinotifikasikan ke tim yang bertugas.')
                        ->success()
                        ->send();

                    $this->refreshFormData(['nomor_surat', 'file_tte', 'status']);
                }),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->wasChanged('file_tte') && filled($this->record->file_tte)) {
            $this->record->kirimNotifikasiKeTim();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
