<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Users\Pages\ViewUser;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class RiwayatPendidikanRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatPendidikan';

    protected static ?string $title = 'Riwayat Pendidikan';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === ViewUser::class;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jenjang')
                    ->label('Jenjang')
                    ->badge()
                    ->color('info'),

                TextColumn::make('nama_institusi')
                    ->label('Nama Sekolah / Perguruan Tinggi')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('jurusan')
                    ->label('Jurusan / Program Studi')
                    ->placeholder('-')
                    ->wrap(),

                TextColumn::make('tahun_lulus')
                    ->label('Tahun Lulus')
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),

                TextColumn::make('nomor_ijazah')
                    ->label('Nomor Seri Ijazah')
                    ->placeholder('-'),

                IconColumn::make('is_terakhir')
                    ->label('Terakhir')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->recordActions([
                Action::make('preview_pdf')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->visible(fn (Model $record): bool => filled($record->file_ijazah))
                    ->modalHeading(fn (Model $record): string => 'Ijazah — ' . ($record->nama_institusi ?? ''))
                    ->modalWidth(Width::FiveExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function (Model $record): HtmlString {
                        $url = asset('storage/' . ltrim((string) $record->file_ijazah, '/'));

                        return new HtmlString(
                            '<iframe src="' . e($url) . '" class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white" style="height: 75vh;"></iframe>'
                        );
                    }),

                Action::make('unduh_pdf')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (Model $record): bool => filled($record->file_ijazah))
                    ->action(function (Model $record) {
                        $path = (string) ($record->file_ijazah ?? '');
                        if ($path === '' || ! Storage::disk('public')->exists($path)) {
                            Notification::make()->title('Berkas PDF tidak ditemukan di server.')->danger()->send();

                            return null;
                        }

                        return response()->download(Storage::disk('public')->path($path));
                    }),
            ])
            ->paginated(false);
    }
}
