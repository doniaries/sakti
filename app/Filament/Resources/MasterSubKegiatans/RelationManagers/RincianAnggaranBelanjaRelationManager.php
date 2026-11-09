<?php

namespace App\Filament\Resources\MasterSubKegiatans\RelationManagers;

use App\Enums\SumberDana;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Models\KodeRekening;
use App\Models\MasterBarang;
use App\Models\MasterSubKegiatan;
use App\Models\RincianAnggaranBelanja;
use App\Models\Satuan;
use App\Support\FormatHelper;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RincianAnggaranBelanjaRelationManager extends RelationManager
{
    protected static string $relationship = 'rincianAnggarans';

    protected static ?string $title = 'Rincian Belanja (RKA)';

    protected static ?string $modelLabel = 'Item Belanja';

    protected static ?string $pluralModelLabel = 'Rincian Belanja';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Rincian Item Belanja RKA')
                    ->description('Kelola rekening belanja, komponen barang/jasa, volume, dan harga standar.')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 12])
                            ->schema([
                                Select::make('sumber_dana')
                                    ->label('Sumber Pendanaan')
                                    ->options(SumberDana::class)
                                    ->default(SumberDana::DAU->value)
                                    ->required()
                                    ->columnSpan(['default' => 12, 'md' => 4]),

                                Select::make('kode_rekening_id')
                                    ->label('Sub Rincian Obyek (Rekening Belanja)')
                                    ->placeholder('Pilih Sub Rincian Obyek Belanja...')
                                    ->searchable()
                                    ->preload()
                                    ->options(function () {
                                        return KodeRekening::hanyaSubRincianObyek()
                                            ->where('kode_lengkap', 'like', '5.1.02%')
                                            ->orderBy('kode_lengkap')
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(fn ($r) => [$r->id => "{$r->kode_lengkap} - {$r->uraian}"])
                                            ->all();
                                    })
                                    ->getSearchResultsUsing(function (string $search): array {
                                        $tokens = explode(' ', trim($search));

                                        return KodeRekening::hanyaSubRincianObyek()
                                            ->where(function ($q) use ($tokens, $search) {
                                                $q->where('kode_lengkap', 'like', "%{$search}%");
                                                $q->orWhere(function ($sub) use ($tokens) {
                                                    foreach ($tokens as $token) {
                                                        if (filled($token)) {
                                                            $sub->where('uraian', 'like', "%{$token}%");
                                                        }
                                                    }
                                                });
                                            })
                                            ->orderBy('kode_lengkap')
                                            ->limit(40)
                                            ->get()
                                            ->mapWithKeys(fn ($r) => [$r->id => "{$r->kode_lengkap} - {$r->uraian}"])
                                            ->all();
                                    })
                                    ->getOptionLabelUsing(fn ($value): ?string => ($r = KodeRekening::find($value)) ? "{$r->kode_lengkap} - {$r->uraian}" : null)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $set('master_barang_id', null);
                                    })
                                    ->columnSpan(['default' => 12, 'md' => 8]),

                                Select::make('master_barang_id')
                                    ->label('Komponen Belanja / Barang')
                                    ->placeholder(fn (Get $get): string => filled($get('kode_rekening_id')) ? 'Pilih komponen barang standar...' : 'Pilih Sub Rincian Obyek terlebih dahulu')
                                    ->helperText(fn (Get $get): string => filled($get('kode_rekening_id')) ? 'Pilih komponen standar DPA/SSH atau klik (+) untuk menambah barang baru.' : 'Pilih Sub Rincian Obyek terlebih dahulu.')
                                    ->preload()
                                    ->options(function (Get $get) {
                                        $rekId = $get('kode_rekening_id');
                                        if (! $rekId) {
                                            return [];
                                        }

                                        return MasterBarang::where('kode_rekening_id', $rekId)
                                            ->with('satuan')
                                            ->orderBy('nama_barang')
                                            ->get()
                                            ->mapWithKeys(fn ($b) => [$b->id => "{$b->nama_barang}" . ($b->satuan ? " ({$b->satuan->nama})" : '')])
                                            ->all();
                                    })
                                    ->getSearchResultsUsing(function (string $search, Get $get): array {
                                        $rekId = $get('kode_rekening_id');
                                        if (! $rekId) {
                                            return [];
                                        }

                                        return MasterBarang::where('kode_rekening_id', $rekId)
                                            ->with('satuan')
                                            ->where('nama_barang', 'like', "%{$search}%")
                                            ->orderBy('nama_barang')
                                            ->limit(30)
                                            ->get()
                                            ->mapWithKeys(fn ($b) => [$b->id => "{$b->nama_barang}" . ($b->satuan ? " ({$b->satuan->nama})" : '')])
                                            ->all();
                                    })
                                    ->getOptionLabelUsing(fn ($value): ?string => ($b = MasterBarang::with('satuan')->find($value)) ? "{$b->nama_barang}" . ($b->satuan ? " ({$b->satuan->nama})" : '') : null)
                                    ->searchable()
                                    ->createOptionForm([
                                        TextInput::make('nama_barang')
                                            ->label('Nama Komponen Barang')
                                            ->placeholder('Contoh: Kertas HVS Sidu F4 75 gram')
                                            ->required(),
                                        Select::make('satuan_id')
                                            ->label('Satuan')
                                            ->options(Satuan::pluck('nama', 'id')->all())
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                    ])
                                    ->createOptionUsing(function (array $data, Get $get): int {
                                        $data['kode_rekening_id'] = $get('kode_rekening_id');
                                        $barang = MasterBarang::create($data);

                                        return $barang->id;
                                    })
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if ($state) {
                                            $barang = MasterBarang::with('satuan')->find($state);
                                            if ($barang) {
                                                $set('nama_item', $barang->nama_barang);
                                                if ($barang->satuan) {
                                                    $set('satuan', $barang->satuan->nama);
                                                }
                                                $vol = max(1, (float) ($get('volume') ?: 1));
                                                $set('volume', $vol);
                                                $harga = FormatHelper::parseRupiah($get('harga_satuan') ?: 0);
                                                $pagu = (int) round($vol * $harga);
                                                $set('pagu_total', $pagu > 0 ? number_format($pagu, 0, ',', '.') : 0);
                                            }
                                        }
                                    })
                                    ->required()
                                    ->columnSpan(['default' => 12, 'md' => 12]),

                                Hidden::make('nama_item')
                                    ->dehydrated(true),

                                TextInput::make('spesifikasi')
                                    ->label('Spesifikasi / Rincian Teknis (Opsional)')
                                    ->placeholder('Contoh: Warna Putih, 75 GSM, Ukuran Folio / Rincian kegiatan...')
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 12, 'md' => 12]),
                            ]),

                        Grid::make(['default' => 1, 'sm' => 2, 'md' => 4])
                            ->schema([
                                TextInput::make('volume')
                                    ->label('Volume / Koefisien')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live(debounce: 350)
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $vol = (float) ($state ?: 0);
                                        $harga = FormatHelper::parseRupiah($get('harga_satuan'));
                                        $pagu = (int) round($vol * $harga);
                                        $set('pagu_total', $pagu > 0 ? number_format($pagu, 0, ',', '.') : 0);
                                    }),

                                TextInput::make('satuan')
                                    ->label('Satuan')
                                    ->placeholder('Rim / Kotak / Lembar')
                                    ->required(),

                                FormatHelper::rupiahInput('harga_satuan', 'Harga Satuan (RKA)')
                                    ->required()
                                    ->live(debounce: 350)
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $vol = (float) ($get('volume') ?: 0);
                                        $harga = FormatHelper::parseRupiah($state);
                                        $pagu = (int) round($vol * $harga);
                                        $set('pagu_total', $pagu > 0 ? number_format($pagu, 0, ',', '.') : 0);
                                    }),

                                FormatHelper::rupiahInput('pagu_total', 'Total Pagu Anggaran')
                                    ->readOnly()
                                    ->extraInputAttributes(['class' => 'font-bold text-amber-500 transition-all duration-300 ease-in-out'])
                                    ->dehydrated(true),
                            ]),

                        Textarea::make('keterangan')
                            ->label('Catatan Tambahan (Opsional)')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function getTableDescription(): ?string
    {
        /** @var MasterSubKegiatan|null $owner */
        $owner = $this->getOwnerRecord();
        if ($owner?->is_locked) {
            return '🔒 Status RKA: TERKUNCI (Baku Sesuai DPA). Rincian belanja terkunci dari perubahan data. User hanya dapat memproses Pengajuan SPJ dari item belanja ini.';
        }

        return '🔓 Status RKA: TERBUKA (Mode Perubahan / Pergeseran Anggaran). Anda dapat menambah item belanja, menyesuaikan harga satuan, volume, atau merevisi data belanja.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_item')
            ->recordActionsPosition(RecordActionsPosition::BeforeColumns)
            ->recordActionsColumnLabel('Aksi')
            ->stickyableColumns()
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->with(['kodeRekening', 'masterBarang.satuan'])
                    ->withSum(['spjDetails as volume_teralokasi' => function ($q) {
                        $q->whereHas('pengajuanSpj', fn ($sq) => $sq->whereNotIn('status', ['draft', 'ditolak']));
                    }], 'kuantitas')
                    ->withSum(['spjDetails as nilai_teralokasi' => function ($q) {
                        $q->whereHas('pengajuanSpj', fn ($sq) => $sq->whereNotIn('status', ['draft', 'ditolak']));
                    }], 'subtotal');
            })
            ->columns([
                TextColumn::make('nama_item')
                    ->label('Uraian Barang / Belanja')
                    ->description(fn (RincianAnggaranBelanja $r): ?string => $r->spesifikasi)
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap()
                    ->extraAttributes(['style' => 'min-width: 260px;'], merge: true)
                    ->extraHeaderAttributes(['style' => 'min-width: 260px;'], merge: true)
                    ->sticky(),

                TextColumn::make('sisa_anggaran')
                    ->label('Sisa Anggaran')
                    ->state(fn (RincianAnggaranBelanja $r): string => FormatHelper::rupiah($r->sisa_anggaran))
                    ->description(function (RincianAnggaranBelanja $r): string {
                        $pagu = (float) $r->pagu_total;
                        $terpakai = max((float) $r->nilai_realisasi, (float) $r->nilai_teralokasi);
                        $persenSisa = $pagu > 0 ? round(($r->sisa_anggaran / $pagu) * 100) : 0;

                        if ($terpakai <= 0) {
                            return "100% Pagu Utuh ({$r->sisa_volume} {$r->satuan})";
                        }

                        return "Tersisa {$persenSisa}% • Terpakai " . FormatHelper::rupiah($terpakai);
                    })
                    ->color(fn (RincianAnggaranBelanja $r): string => $r->sisa_anggaran > 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->alignEnd(),

                TextColumn::make('sisa_volume')
                    ->label('Sisa Stok')
                    ->state(fn (RincianAnggaranBelanja $r): string => number_format($r->sisa_volume, 0, ',', '.') . ' ' . $r->satuan)
                    ->badge()
                    ->color(fn (RincianAnggaranBelanja $r) => $r->sisa_volume > 0 ? 'success' : 'danger')
                    ->icon(fn (RincianAnggaranBelanja $r) => $r->sisa_volume > 0 ? 'heroicon-m-cube' : 'heroicon-m-exclamation-circle')
                    ->alignCenter(),

                TextColumn::make('harga_satuan')
                    ->label('Harga Standar')
                    ->formatStateUsing(fn ($state) => FormatHelper::rupiah((float) $state))
                    ->alignEnd(),

                TextColumn::make('pagu_total')
                    ->label('Total Pagu')
                    ->formatStateUsing(fn ($state) => FormatHelper::rupiah((float) $state))
                    ->weight('bold')
                    ->alignEnd()
                    ->summarize(
                        Sum::make()
                            ->label('Total Pagu Sub Kegiatan')
                            ->formatStateUsing(fn ($state) => FormatHelper::rupiah((float) $state))
                    ),

                TextColumn::make('nilai_realisasi')
                    ->label('Realisasi SPJ')
                    ->formatStateUsing(fn ($state) => FormatHelper::rupiah((float) $state))
                    ->alignEnd()
                    ->summarize(
                        Sum::make()
                            ->label('Total Realisasi')
                            ->formatStateUsing(fn ($state) => FormatHelper::rupiah((float) $state))
                    ),

                TextColumn::make('kodeRekening.uraian')
                    ->label('Sub Rincian Obyek')
                    ->description(fn (RincianAnggaranBelanja $r): ?string => $r->kodeRekening?->kode_lengkap)
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->extraAttributes(['style' => 'min-width: 240px;'])
                    ->extraHeaderAttributes(['style' => 'min-width: 240px;']),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Item Belanja RKA')
                    ->icon('heroicon-o-plus')
                    ->slideOver()
                    ->modalWidth('4xl')
                    ->visible(fn (): bool => ! (bool) ($this->getOwnerRecord()?->is_locked))
                    ->mutateFormDataUsing(function (array $data): array {
                        /** @var MasterSubKegiatan $owner */
                        $owner = $this->getOwnerRecord();
                        $data['sub_kegiatan_id'] = $owner->id;
                        $data['tahun_anggaran'] = $owner->tahun_anggaran ?: (int) date('Y');
                        $data['bidang_id'] = $owner->kegiatan?->bidang_id;
                        $data['volume_realisasi'] = 0;
                        $data['nilai_realisasi'] = 0;

                        if (empty($data['nama_item']) && ! empty($data['master_barang_id'])) {
                            $data['nama_item'] = MasterBarang::find($data['master_barang_id'])?->nama_barang;
                        }

                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('ajukan_spj')
                    ->label('Ajukan SPJ')
                    ->icon('heroicon-o-document-currency-dollar')
                    ->color('success')
                    ->url(fn (RincianAnggaranBelanja $record): string => PengajuanSpjResource::getUrl('create', ['rka_id' => $record->id]))
                    ->openUrlInNewTab()
                    ->visible(fn (RincianAnggaranBelanja $record): bool => $record->sisa_volume > 0 && $record->sisa_anggaran > 0),

                EditAction::make()
                    ->slideOver()
                    ->modalWidth('4xl')
                    ->visible(fn (RincianAnggaranBelanja $record): bool => ! (bool) ($record->subKegiatan?->is_locked ?? $this->getOwnerRecord()?->is_locked))
                    ->mutateFormDataUsing(function (array $data): array {
                        if (empty($data['nama_item']) && ! empty($data['master_barang_id'])) {
                            $data['nama_item'] = MasterBarang::find($data['master_barang_id'])?->nama_barang;
                        }

                        return $data;
                    }),
                DeleteAction::make()
                    ->visible(fn (RincianAnggaranBelanja $record): bool => ! (bool) ($record->subKegiatan?->is_locked ?? $this->getOwnerRecord()?->is_locked)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => ! (bool) ($this->getOwnerRecord()?->is_locked)),
                ]),
            ]);
    }
}

