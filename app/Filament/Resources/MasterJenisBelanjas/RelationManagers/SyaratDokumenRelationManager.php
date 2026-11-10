<?php

namespace App\Filament\Resources\MasterJenisBelanjas\RelationManagers;

use App\Models\MasterSyaratBelanja;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SyaratDokumenRelationManager extends RelationManager
{
    protected static string $relationship = 'syaratBelanjas';

    protected static ?string $title = 'Daftar Syarat Dokumen SPJ';

    protected static ?string $modelLabel = 'Syarat Dokumen';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rincian Dokumen Persyaratan')
                    ->schema([
                        TextInput::make('nama_syarat')
                            ->label('Nama Dokumen Persyaratan')
                            ->placeholder('Contoh: Faktur / Nota Toko Asli')
                            ->helperText('Ketik nama dokumen baru atau pilih dari daftar syarat yang pernah dibuat.')
                            ->datalist(fn () => MasterSyaratBelanja::query()
                                ->whereNotNull('nama_syarat')
                                ->where('nama_syarat', '!=', '')
                                ->distinct()
                                ->orderBy('nama_syarat')
                                ->pluck('nama_syarat')
                                ->all()
                            )
                            ->live(debounce: 350)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (! empty($state) && empty($get('keterangan'))) {
                                    $sampleKeterangan = MasterSyaratBelanja::where('nama_syarat', $state)
                                        ->whereNotNull('keterangan')
                                        ->where('keterangan', '!=', '')
                                        ->value('keterangan');
                                    if ($sampleKeterangan) {
                                        $set('keterangan', $sampleKeterangan);
                                    }
                                }
                            })
                            ->required()
                            ->maxLength(255)
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        $owner = $this->getOwnerRecord();
                                        $ownerId = $owner?->id;
                                        $record = $this->getMountedTableActionRecord();
                                        $recordId = $record?->id;

                                        $exists = MasterSyaratBelanja::query()
                                            ->when($ownerId, fn ($q) => $q->where('master_jenis_belanja_id', $ownerId))
                                            ->whereRaw('LOWER(TRIM(nama_syarat)) = ?', [strtolower(trim((string) $value))])
                                            ->when($recordId, fn ($q) => $q->where('id', '!=', $recordId))
                                            ->exists();

                                        if ($exists) {
                                            $fail("Dokumen '{$value}' sudah terdaftar pada jenis belanja ini. Dokumen tidak boleh duplikat/berulang.");
                                        }
                                    };
                                },
                            ]),

                        Textarea::make('keterangan')
                            ->label('Petunjuk / Keterangan Dokumen (Opsional)')
                            ->placeholder('Contoh: Scan dokumen PDF asli bertandatangan dan berstempel')
                            ->rows(2),

                        Toggle::make('is_required')
                            ->label('Wajib Diunggah')
                            ->helperText('Jika diaktifkan, berkas ini menjadi syarat mutlak (wajib). Jika tidak, berkas bersifat opsional.')
                            ->default(true),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('urutan')
            ->defaultSort('urutan')
            ->columns([
                TextColumn::make('nama_syarat')
                    ->label('Nama Dokumen Persyaratan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn(MasterSyaratBelanja $record): ?string => $record->keterangan)
                    ->wrap(),

                ToggleColumn::make('is_required')
                    ->label('Wajib')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('pilih_syarat_standar')
                    ->label('Pilih dari Syarat Standar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->modalHeading('Pilih Dokumen Persyaratan Standar')
                    ->modalDescription('Centang dokumen yang ingin dimasukkan ke dalam jenis belanja ini. Dokumen yang sudah ada otomatis disaring agar tidak duplikat.')
                    ->modalWidth('2xl')
                    ->form([
                        CheckboxList::make('dokumen_terpilih')
                            ->label('Daftar Dokumen Yang Tersedia')
                            ->options(function (): array {
                                $owner = $this->getOwnerRecord();
                                $existingNames = MasterSyaratBelanja::where('master_jenis_belanja_id', $owner->id)
                                    ->pluck('nama_syarat')
                                    ->map(fn ($n) => strtolower(trim($n)))
                                    ->all();

                                return MasterSyaratBelanja::query()
                                    ->distinct()
                                    ->whereNotNull('nama_syarat')
                                    ->where('nama_syarat', '!=', '')
                                    ->orderBy('nama_syarat')
                                    ->pluck('nama_syarat', 'nama_syarat')
                                    ->reject(fn ($label, $val) => in_array(strtolower(trim($val)), $existingNames, true))
                                    ->all();
                            })
                            ->columns(1)
                            ->bulkToggleable()
                            ->searchable()
                            ->required(),

                        Toggle::make('is_required_default')
                            ->label('Tandai sebagai Wajib Diunggah')
                            ->default(true),
                    ])
                    ->action(function (array $data): void {
                        $owner = $this->getOwnerRecord();
                        $dokumenList = (array) ($data['dokumen_terpilih'] ?? []);
                        $isRequired = (bool) ($data['is_required_default'] ?? true);

                        $maxUrutan = MasterSyaratBelanja::where('master_jenis_belanja_id', $owner->id)->max('urutan') ?? 0;

                        $count = 0;
                        foreach ($dokumenList as $namaDokumen) {
                            $existing = MasterSyaratBelanja::where('master_jenis_belanja_id', $owner->id)
                                ->whereRaw('LOWER(TRIM(nama_syarat)) = ?', [strtolower(trim($namaDokumen))])
                                ->exists();

                            if (! $existing) {
                                $maxUrutan++;
                                $sampleKeterangan = MasterSyaratBelanja::where('nama_syarat', $namaDokumen)
                                    ->whereNotNull('keterangan')
                                    ->value('keterangan');

                                MasterSyaratBelanja::create([
                                    'master_jenis_belanja_id' => $owner->id,
                                    'jenis_belanja' => $owner->kode,
                                    'metode_pengadaan' => $owner->metode_pengadaan,
                                    'nama_syarat' => $namaDokumen,
                                    'keterangan' => $sampleKeterangan,
                                    'is_required' => $isRequired,
                                    'urutan' => $maxUrutan,
                                    'aktif' => true,
                                ]);
                                $count++;
                            }
                        }

                        Notification::make()
                            ->success()
                            ->title('Syarat Berhasil Ditambahkan')
                            ->body("{$count} dokumen persyaratan berhasil dimasukkan tanpa duplikasi.")
                            ->send();
                    }),

                CreateAction::make()
                    ->label('Tambah Syarat Baru')
                    ->icon('heroicon-o-plus')
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data): array {
                        $parent = $this->getOwnerRecord();
                        $data['jenis_belanja'] = $parent->kode;
                        $data['metode_pengadaan'] = $parent->metode_pengadaan;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ]);
    }
}
