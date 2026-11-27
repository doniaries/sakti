<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RiwayatSertifikatRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatSertifikat';

    protected static ?string $title = 'Pendidikan PNJJ / Diklat & Sertifikat';

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
                TextInput::make('nama_sertifikat')
                    ->label('Nama Diklat / PNJJ (Contoh: PIM II, PIM IV, LATSAR, LPJ)')
                    ->required()
                    ->maxLength(255),

                Select::make('jenis')
                    ->label('Jenis Diklat / Sertifikat')
                    ->options([
                        'pnjj' => 'Pendidikan PNJJ / Struktural (PIM / LATSAR / LPJ)',
                        'teknis' => 'Diklat Teknis',
                        'fungsional' => 'Diklat Fungsional',
                        'lainnya' => 'Lainnya',
                    ])
                    ->default('pnjj')
                    ->required(),

                TextInput::make('penyelenggara')
                    ->label('Penyelenggara')
                    ->maxLength(255),

                TextInput::make('nomor_sertifikat')
                    ->label('Nomor Sertifikat / STTPP')
                    ->maxLength(100),

                DatePicker::make('tanggal_terbit')
                    ->label('Tanggal / Tahun Lulus')
                    ->displayFormat('d/m/Y'),

                TextInput::make('jumlah_jam')
                    ->label('Jumlah Jam (JP)')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('Jam'),

                FileUpload::make('file_sertifikat')
                    ->label('Berkas Sertifikat (PDF, Maks. 2 MB)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(2048)
                    ->disk('public')
                    ->directory(fn (): string => "dokumen_pegawai/{$this->getOwnerRecord()->getKey()}/sertifikat")
                    ->preserveFilenames(false)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_sertifikat')
                    ->label('Nama Diklat / PNJJ')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pnjj' => 'PNJJ / Struktural',
                        'teknis' => 'Teknis',
                        'fungsional' => 'Fungsional',
                        default => $state ?: '-',
                    })
                    ->color(fn (?string $state): string => $state === 'pnjj' ? 'info' : 'gray'),

                TextColumn::make('tanggal_terbit')
                    ->label('Tahun / Tanggal')
                    ->date('d/m/Y')
                    ->placeholder('-'),

                TextColumn::make('jumlah_jam')
                    ->label('Jam')
                    ->formatStateUsing(fn ($state): string => filled($state) ? "{$state} Jam" : '-')
                    ->placeholder('-'),

                TextColumn::make('nomor_sertifikat')
                    ->label('Nomor')
                    ->placeholder('-'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Diklat / PNJJ')
                    ->icon('heroicon-o-plus-circle'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->paginated(false);
    }
}
