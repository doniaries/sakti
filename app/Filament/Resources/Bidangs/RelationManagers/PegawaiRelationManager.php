<?php

namespace App\Filament\Resources\Bidangs\RelationManagers;

use App\Models\Bidang;
use App\Models\Jabatan;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class PegawaiRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Daftar Pegawai / Staf';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                TextInput::make('nip')
                    ->label('NIP')
                    ->maxLength(30),

                Select::make('jabatan_id')
                    ->label('Jabatan')
                    ->options(function (RelationManager $livewire): array {
                        /** @var Bidang|null $bidang */
                        $bidang = $livewire->getOwnerRecord();
                        return ($bidang?->getJabatansTerkait() ?? Jabatan::where('nama', 'NOT LIKE', '%Kepala Dinas%')->pluck('nama', 'id'))->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('status_kerja')
                    ->label('Tipe Kerja')
                    ->options([
                        'wfo' => 'WFO (Work From Office)',
                        'wfh' => 'WFH (Work From Home)',
                    ])
                    ->default('wfo')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('jabatan.nama')
                    ->label('Jabatan')
                    ->badge()
                    ->color(fn($state) => match (true) {
                        str_contains($state ?? '', 'Kepala Dinas') => 'danger',
                        str_contains($state ?? '', 'Sekretaris') => 'warning',
                        str_contains($state ?? '', 'Kabid') => 'purple',
                        str_contains($state ?? '', 'Kasubbag') => 'amber',
                        str_contains($state ?? '', 'Adyatama') => 'rose',
                        str_contains($state ?? '', 'Pelatih') || str_contains($state ?? '', 'Olahraga') => 'teal',
                        str_contains($state ?? '', 'Komputer') || str_contains($state ?? '', 'Sistem') => 'cyan',
                        str_contains($state ?? '', 'Perencana') => 'indigo',
                        str_contains($state ?? '', 'Konservator') => 'emerald',
                        str_contains($state ?? '', 'Hubungan') || str_contains($state ?? '', 'Penerjemah') => 'violet',
                        str_contains($state ?? '', 'Analis Kebijakan') => 'info',
                        str_contains($state ?? '', 'Penelaah Teknis') => 'blue',
                        str_contains($state ?? '', 'Pengelola') || str_contains($state ?? '', 'Penata') => 'lime',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('atasan.name')
                    ->label('Atasan Langsung')
                    ->placeholder('-'),

                TextColumn::make('status_kerja')
                    ->label('Tipe Kerja')
                    ->badge()
                    ->color(fn($state) => match (strtolower($state ?? 'wfo')) {
                        'wfh' => 'warning',
                        default => 'success',
                    })
                    ->icon(fn($state) => match (strtolower($state ?? 'wfo')) {
                        'wfh' => 'heroicon-m-home',
                        default => 'heroicon-m-building-office-2',
                    })
                    ->formatStateUsing(fn($state) => strtoupper($state ?? 'WFO')),

                ToggleColumn::make('aktif')
                    ->label('Status Aktif'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AssociateAction::make()
                    ->label('Hubungkan Pegawai ke Bidang Ini')
                    ->modalHeading('Kaitkan Pegawai ke Bidang')
                    ->modalDescription('Pilih pegawai yang belum memiliki bidang dan tentukan jabatan serta tanggung jawabnya.')
                    ->modalSubmitActionLabel('Kaitkan Pegawai')
                    ->recordSelectOptionsQuery(fn($query) => $query->whereNull('bidang_id')->bukanSuperAdmin())
                    ->recordTitle(fn($record) => "{$record->name}" . ($record->nip ? " (NIP: {$record->nip})" : ''))
                    ->recordSelectSearchColumns(['name', 'nip'])
                    ->preloadRecordSelect()
                    ->schema(function (AssociateAction $action, PegawaiRelationManager $livewire): array {
                        /** @var Bidang|null $bidang */
                        $bidang = $livewire->getOwnerRecord();

                        $jabatanOptions = ($bidang?->getJabatansTerkait() ?? Jabatan::where('nama', 'NOT LIKE', '%Kepala Dinas%')->pluck('nama', 'id'))->toArray();

                        return [
                            $action->getRecordSelect()
                                ->label('Pilih Pegawai (Belum Memiliki Bidang)')
                                ->placeholder('Pilih salah satu pegawai...')
                                ->helperText('Hanya pegawai yang belum ditempatkan pada bidang mana pun yang tampil di sini.')
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $user = User::find($state);
                                        if ($user?->jabatan_id) {
                                            $set('jabatan_id', $user->jabatan_id);
                                        }
                                    }
                                }),

                            Select::make('jabatan_id')
                                ->label('Jabatan di Bidang Ini')
                                ->placeholder('Pilih jabatan terkait...')
                                ->helperText('Daftar jabatan disesuaikan khusus untuk lingkungan ' . ($bidang->nama ?? 'bidang ini') . '.')
                                ->options($jabatanOptions)
                                ->searchable()
                                ->preload()
                                ->required(),
                        ];
                    })
                    ->using(function (array $data, AssociateAction $action, User $record, $inverseRelationship, PegawaiRelationManager $livewire): void {
                        /** @var Bidang $bidang */
                        $bidang = $livewire->getOwnerRecord();

                        $inverseRelationship->associate($bidang);

                        if (!empty($data['jabatan_id'])) {
                            $record->jabatan_id = $data['jabatan_id'];
                        }

                        // Hubungkan atasan langsung ke Kepala Bidang secara otomatis
                        if ($bidang->kepala_id && $record->id !== $bidang->kepala_id) {
                            $record->atasan_id = $bidang->kepala_id;
                        }

                        $record->save();
                    }),
            ])
            ->recordActions([
                EditAction::make(),

                // Aksi Pindah Bidang (Mutasi Internal dalam 1 Instansi)
                Action::make('pindah_bidang')
                    ->label('Pindah Bidang')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->modalHeading('Pindah Bidang (Mutasi Internal Dinas)')
                    ->modalDescription('Pindahkan pegawai ini ke bidang lain di dalam dinas/instansi ini.')
                    ->modalSubmitActionLabel('Simpan Pemindahan')
                    ->form(function (PegawaiRelationManager $livewire): array {
                        /** @var Bidang|null $currentBidang */
                        $currentBidang = $livewire->getOwnerRecord();

                        return [
                            Select::make('bidang_id')
                                ->label('Pilih Bidang Tujuan')
                                ->options(Bidang::where('id', '!=', $currentBidang?->id)->pluck('nama', 'id'))
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn(callable $set) => $set('jabatan_id', null)),

                            Select::make('jabatan_id')
                                ->label('Jabatan Baru di Bidang Tujuan')
                                ->options(function (callable $get): array {
                                    $bidangId = $get('bidang_id');
                                    if (!$bidangId) {
                                        return [];
                                    }
                                    $targetBidang = Bidang::find($bidangId);
                                    return ($targetBidang?->getJabatansTerkait() ?? Jabatan::where('nama', 'NOT LIKE', '%Kepala Dinas%')->pluck('nama', 'id'))->toArray();
                                })
                                ->required()
                                ->searchable()
                                ->preload(),
                        ];
                    })
                    ->action(function (array $data, User $record): void {
                        $targetBidang = Bidang::find($data['bidang_id']);

                        $record->update([
                            'bidang_id' => $data['bidang_id'],
                            'jabatan_id' => $data['jabatan_id'],
                            'atasan_id' => ($targetBidang?->kepala_id && $record->id !== $targetBidang->kepala_id) ? $targetBidang->kepala_id : null,
                        ]);

                        Notification::make()
                            ->title('Berhasil Pindah Bidang')
                            ->body("{$record->name} berhasil dipindahkan ke {$targetBidang?->nama}.")
                            ->success()
                            ->send();
                    }),

                // Aksi Pindah Instansi (Mutasi Keluar Dinas / Instansi)
                /* Action::make('pindah_instansi')
                    ->label('Pindah Instansi')
                    ->icon('heroicon-o-arrow-right-start-on-rectangle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Pindah Instansi (Mutasi Keluar Dinas)')
                    ->modalDescription('Pegawai ini mutasi keluar dinas. Data pegawai ini tidak akan terkait lagi di dinas ini (penempatan bidang dihapus dan akun dinonaktifkan).')
                    ->modalSubmitActionLabel('Konfirmasi Pindah Instansi')
                    ->action(function (User $record): void {
                        $record->update([
                            'bidang_id' => null,
                            'atasan_id' => null,
                            'aktif' => false,
                        ]);

                        Notification::make()
                            ->title('Pegawai Telah Pindah Instansi')
                            ->body("{$record->name} telah dikeluarkan dari dinas ini dan status akun dinonaktifkan.")
                            ->warning()
                            ->send();
                    }), */
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('pindah_instansi_massal')
                        ->label('Pindah Instansi (Keluar Dinas)')
                        ->icon('heroicon-o-arrow-right-start-on-rectangle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Pindah Instansi Terpilih (Keluar Dinas)')
                        ->modalDescription('Pegawai terpilih akan dikeluarkan dari dinas ini dan status akun dinonaktifkan.')
                        ->modalSubmitActionLabel('Konfirmasi Pindah Instansi')
                        ->action(function (Collection $records): void {
                            $records->each(fn(User $u) => $u->update([
                                'bidang_id' => null,
                                'atasan_id' => null,
                                'aktif' => false,
                            ]));

                            Notification::make()
                                ->title('Berhasil Memproses Pindah Instansi')
                                ->body(count($records) . ' pegawai telah dikeluarkan dari dinas ini dan dinonaktifkan.')
                                ->warning()
                                ->send();
                        }),
                ]),
            ]);
    }
}
