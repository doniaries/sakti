<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\GolonganRuang;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class RiwayatGolonganRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatGolongan';

    protected static ?string $title = 'Riwayat Pangkat & Golongan';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return in_array($pageClass, [ViewUser::class, EditUser::class], true);
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
                Select::make('jenis_sk')
                    ->label('Jenis SK')
                    ->options([
                        'cpns' => 'SK CPNS (TMT CPNS)',
                        'pns' => 'SK PNS (TMT PNS)',
                        'pppk' => 'SK PPPK (TMT PPPK)',
                        'kenaikan_pangkat' => 'SK Kenaikan Pangkat',
                    ])
                    ->default('kenaikan_pangkat')
                    ->required(),

                Select::make('golongan')
                    ->label('Pangkat / Golongan')
                    ->options(GolonganRuang::class)
                    ->searchable()
                    ->required(),

                DatePicker::make('tmt_golongan')
                    ->label('TMT Golongan / TMT SK')
                    ->displayFormat('d/m/Y')
                    ->required(),

                TextInput::make('nomor_sk')
                    ->label('Nomor SK')
                    ->maxLength(100),

                DatePicker::make('tanggal_sk')
                    ->label('Tanggal SK')
                    ->displayFormat('d/m/Y'),

                TextInput::make('masa_kerja_tahun')
                    ->label('Masa Kerja (Tahun)')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),

                TextInput::make('masa_kerja_bulan')
                    ->label('Masa Kerja (Bulan)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(11)
                    ->default(0),

                Toggle::make('is_golongan_terakhir')
                    ->label('Tandai sebagai Pangkat Terakhir')
                    ->helperText('Otomatis menjadi Realisasi Pangkat Terakhir & Masa Kerja di Bezeting Pegawai.')
                    ->default(false)
                    ->columnSpanFull(),

                FileUpload::make('file_sk_golongan')
                    ->label('Berkas SK Pangkat (PDF, Maks. 2 MB)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(2048)
                    ->disk('public')
                    ->directory(fn (): string => "dokumen_pegawai/{$this->getOwnerRecord()->getKey()}/sk_pangkat")
                    ->preserveFilenames(false)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jenis_sk')
                    ->label('Jenis SK')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cpns' => 'CPNS',
                        'pns' => 'PNS',
                        'pppk' => 'PPPK',
                        'kenaikan_pangkat' => 'Kenaikan Pangkat',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'cpns' => 'info',
                        'pns' => 'success',
                        'pppk' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('golongan')
                    ->label('Pangkat / Golongan')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('tmt_golongan')
                    ->label('TMT Golongan')
                    ->date('d/m/Y')
                    ->placeholder('-'),

                TextColumn::make('masa_kerja_tahun')
                    ->label('Masa Kerja')
                    ->formatStateUsing(fn ($record): string => filled($record->masa_kerja_tahun) || filled($record->masa_kerja_bulan)
                        ? ((int) ($record->masa_kerja_tahun ?? 0)) . ' Thn ' . ((int) ($record->masa_kerja_bulan ?? 0)) . ' Bln'
                        : '-'),

                TextColumn::make('nomor_sk')
                    ->label('Nomor SK')
                    ->placeholder('-'),

                TextColumn::make('tanggal_sk')
                    ->label('Tanggal SK')
                    ->date('d/m/Y')
                    ->placeholder('-'),

                ToggleColumn::make('is_golongan_terakhir')
                    ->label('Pangkat Terakhir'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Pangkat / Golongan')
                    ->icon('heroicon-o-plus-circle'),
            ])
            ->recordActions([
                Action::make('preview_pdf')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->visible(fn (Model $record): bool => filled($record->file_sk))
                    ->modalHeading(fn (Model $record): string => 'SK Pangkat — ' . ($record->golongan?->getLabel() ?? $record->golongan ?? ''))
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
