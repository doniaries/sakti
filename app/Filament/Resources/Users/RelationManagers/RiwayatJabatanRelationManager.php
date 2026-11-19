<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Users\Pages\ViewUser;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class RiwayatJabatanRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatJabatan';

    protected static ?string $title = 'Riwayat Jabatan';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === ViewUser::class;
    }

    public function isReadOnly(): bool
    {
        /** @var User|null $authUser */
        $authUser = Auth::user();

        return ! ($authUser?->can('update', $this->getOwnerRecord()) ?? false);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_jabatan')
                    ->label('Nama Jabatan')
                    ->required()
                    ->maxLength(255),

                TextInput::make('unit_kerja')
                    ->label('Unit Kerja')
                    ->maxLength(255),

                DatePicker::make('tmt_jabatan')
                    ->label('TMT Jabatan')
                    ->displayFormat('d/m/Y'),

                TextInput::make('nomor_sk')
                    ->label('Nomor SK')
                    ->maxLength(100),

                DatePicker::make('tanggal_sk')
                    ->label('Tanggal SK')
                    ->displayFormat('d/m/Y'),

                FileUpload::make('file_sk')
                    ->label('Berkas SK Jabatan (PDF, Maks. 2 MB)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(2048)
                    ->disk('public')
                    ->directory(fn (): string => "dokumen_pegawai/{$this->getOwnerRecord()->getKey()}/sk_jabatan")
                    ->preserveFilenames(false)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_jabatan')
                    ->label('Nama Jabatan')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('unit_kerja')
                    ->label('Unit Kerja')
                    ->placeholder('-')
                    ->wrap(),

                TextColumn::make('tmt_jabatan')
                    ->label('TMT Jabatan')
                    ->date('d/m/Y')
                    ->placeholder('-'),

                TextColumn::make('nomor_sk')
                    ->label('Nomor SK')
                    ->placeholder('-'),

                TextColumn::make('tanggal_sk')
                    ->label('Tanggal SK')
                    ->date('d/m/Y')
                    ->placeholder('-'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Jabatan')
                    ->icon('heroicon-o-plus-circle'),
            ])
            ->recordActions([
                Action::make('preview_pdf')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->visible(fn (Model $record): bool => filled($record->file_sk))
                    ->modalHeading(fn (Model $record): string => 'SK Jabatan — ' . ($record->nama_jabatan ?? ''))
                    ->modalWidth(Width::FiveExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function (Model $record): HtmlString {
                        $url = route('admin.pegawai.berkas.preview', ['path' => (string) $record->file_sk]);

                        return new HtmlString(
                            '<div x-data="{ url: null, loading: true, error: false }" x-init="fetch(' . e(json_encode($url)) . ', { credentials: \'same-origin\' }).then(r => { if (!r.ok) throw new Error(); return r.arrayBuffer(); }).then(buf => { url = URL.createObjectURL(new Blob([buf], { type: \'application/pdf\' })); loading = false; }).catch(() => { error = true; loading = false; })" class="w-full">'
                            . '<template x-if="loading"><div class="flex items-center justify-center text-sm text-gray-500" style="height: 75vh;">Memuat pratinjau dokumen PDF...</div></template>'
                            . '<template x-if="error"><div class="flex items-center justify-center text-sm text-danger-600" style="height: 75vh;">Gagal memuat pratinjau PDF. Silakan gunakan tombol Unduh.</div></template>'
                            . '<template x-if="url"><iframe :src="url" class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white" style="height: 75vh;"></iframe></template>'
                            . '</div>'
                        );
                    }),

                Action::make('unduh_pdf')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (Model $record): bool => filled($record->file_sk))
                    ->action(function (Model $record) {
                        $path = (string) ($record->file_sk ?? '');
                        if ($path === '' || ! Storage::disk('public')->exists($path)) {
                            Notification::make()->title('Berkas PDF tidak ditemukan di server.')->danger()->send();

                            return null;
                        }

                        return response()->download(Storage::disk('public')->path($path));
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->paginated(false);
    }
}
