<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\JenjangPendidikan;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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
                Select::make('jenjang')
                    ->label('Jenjang Pendidikan')
                    ->options(function (?Model $record): array {
                        /** @var User $owner */
                        $owner = $this->getOwnerRecord();
                        $currentVal = $record?->jenjang instanceof JenjangPendidikan
                            ? $record->jenjang->value
                            : (string) ($record?->jenjang ?? '');

                        $existing = $owner->riwayatPendidikan()
                            ->pluck('jenjang')
                            ->map(fn ($v) => $v instanceof JenjangPendidikan ? $v->value : (string) $v)
                            ->filter(fn (string $v): bool => $v !== '' && $v !== $currentVal)
                            ->all();

                        $options = [];
                        foreach (JenjangPendidikan::cases() as $case) {
                            if (! in_array($case->value, $existing, true)) {
                                $options[$case->value] = $case->getLabel();
                            }
                        }

                        return $options;
                    })
                    ->required(),

                TextInput::make('nama_institusi')
                    ->label('Nama Sekolah / Perguruan Tinggi')
                    ->required()
                    ->maxLength(255),

                TextInput::make('jurusan')
                    ->label('Jurusan / Program Studi')
                    ->maxLength(255),

                TextInput::make('tahun_lulus')
                    ->label('Tahun Lulus')
                    ->numeric()
                    ->minValue(1950)
                    ->maxValue((int) date('Y') + 1),

                TextInput::make('nomor_ijazah')
                    ->label('Nomor Seri Ijazah')
                    ->maxLength(100),

                Toggle::make('is_terakhir')
                    ->label('Pendidikan Terakhir / Diakui')
                    ->default(false),

                FileUpload::make('file_ijazah')
                    ->label('Berkas Ijazah (PDF, Maks. 2 MB)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(2048)
                    ->disk('public')
                    ->directory(fn (): string => "dokumen_pegawai/{$this->getOwnerRecord()->getKey()}/ijazah")
                    ->preserveFilenames(false)
                    ->columnSpanFull(),
            ]);
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
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Pendidikan')
                    ->icon('heroicon-o-plus-circle'),
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
                        $url = route('admin.pegawai.berkas.preview', ['path' => (string) $record->file_ijazah]);

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
                    ->visible(fn (Model $record): bool => filled($record->file_ijazah))
                    ->action(function (Model $record) {
                        $path = (string) ($record->file_ijazah ?? '');
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
