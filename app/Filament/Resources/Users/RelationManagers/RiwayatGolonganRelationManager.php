<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\PangkatGolongan;
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

class RiwayatGolonganRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatGolongan';

    protected static ?string $title = 'Riwayat Pangkat & Golongan';

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
                Select::make('golongan')
                    ->label('Pangkat / Golongan')
                    ->options(PangkatGolongan::class)
                    ->required(),

                DatePicker::make('tmt_golongan')
                    ->label('TMT Golongan')
                    ->displayFormat('d/m/Y'),

                TextInput::make('nomor_sk')
                    ->label('Nomor SK')
                    ->maxLength(100),

                DatePicker::make('tanggal_sk')
                    ->label('Tanggal SK')
                    ->displayFormat('d/m/Y'),

                FileUpload::make('file_sk')
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
                TextColumn::make('golongan')
                    ->label('Pangkat / Golongan')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('tmt_golongan')
                    ->label('TMT Golongan')
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
                            '<iframe src="' . e($url) . '" class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white" style="height: 75vh;"></iframe>'
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
