<?php

namespace App\Filament\Resources\Aktivitas\Pages;

use App\Filament\Resources\Aktivitas\AktivitasResource;
use App\Models\Aktivitas;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListAktivitas extends ListRecords
{
    protected static string $resource = AktivitasResource::class;

    protected function getHeaderActions(): array
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser && $currentUser->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian']);

        return [
            Action::make('importBankAktivitas')
                ->label('Impor / Tempel Per User')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('info')
                ->modalHeading('Impor / Tempel Bank Aktivitas Pegawai')
                ->modalDescription('Salin daftar aktivitas dari tabel bankaktifitas.html atau Excel lalu tempel di bawah ini. Sistem otomatis mengaitkan ke pegawai beserta jabatan & bidangnya.')
                ->modalWidth('xl')
                ->form([
                    Select::make('user_id')
                        ->label('Pegawai Pemilik Aktivitas')
                        ->options(fn () => User::orderBy('name')->pluck('name', 'id')->all())
                        ->default($isAdmin ? null : $currentUser?->id)
                        ->disabled(! $isAdmin)
                        ->dehydrated()
                        ->searchable()
                        ->required(),
                    TextInput::make('default_menit')
                        ->label('Durasi Default (Menit)')
                        ->helperText('Dipakai jika baris yang ditempel tidak mencantumkan angka menit.')
                        ->numeric()
                        ->minValue(1)
                        ->default(30)
                        ->required(),
                    Textarea::make('raw_data')
                        ->label('Daftar Aktivitas (Copy-Paste Tabel / Baris)')
                        ->rows(10)
                        ->required()
                        ->placeholder("Contoh format tempelan langsung dari tabel/Excel:\n1\tMengikuti Apel Pagi\t30\n2\tMemimpin rapat/FGD/sosialisasi\t180\natau:\nMengikuti Apel Pagi;30\nMenyusun laporan kegiatan harian (60 menit)"),
                ])
                ->action(function (array $data): void {
                    $targetUserId = (int) ($data['user_id'] ?? Auth::id());
                    $pegawai = User::find($targetUserId);

                    if (! $pegawai) {
                        Notification::make()
                            ->title('Pegawai tidak ditemukan')
                            ->danger()
                            ->send();
                        return;
                    }

                    $items = Aktivitas::parseBulkLines(
                        (string) ($data['raw_data'] ?? ''),
                        max(1, (int) ($data['default_menit'] ?? 30))
                    );

                    if (empty($items)) {
                        Notification::make()
                            ->title('Tidak ada baris valid yang terbaca')
                            ->warning()
                            ->send();
                        return;
                    }

                    $nextKode = (int) Aktivitas::where('user_id', $pegawai->id)->count();
                    $created = 0;
                    $updated = 0;

                    foreach ($items as $item) {
                        $nextKode++;
                        $existing = Aktivitas::where('user_id', $pegawai->id)
                            ->where('nama_aktivitas', $item['nama_aktivitas'])
                            ->first();

                        if ($existing) {
                            $existing->update([
                                'kode' => $item['kode'] ?? $existing->kode,
                                'menit' => $item['menit'],
                                'jabatan_id' => $pegawai->jabatan_id,
                                'bidang_id' => $pegawai->bidang_id,
                            ]);
                            $updated++;
                        } else {
                            Aktivitas::create([
                                'user_id' => $pegawai->id,
                                'jabatan_id' => $pegawai->jabatan_id,
                                'bidang_id' => $pegawai->bidang_id,
                                'kode' => $item['kode'] ?? (string) $nextKode,
                                'nama_aktivitas' => $item['nama_aktivitas'],
                                'menit' => $item['menit'],
                            ]);
                            $created++;
                        }
                    }

                    Notification::make()
                        ->title("Bank Aktivitas {$pegawai->name} Diperbarui")
                        ->body("{$created} aktivitas ditambahkan, {$updated} diperbarui.")
                        ->success()
                        ->send();
                }),
            CreateAction::make()
                ->label('Tambah Aktivitas'),
        ];
    }
}
