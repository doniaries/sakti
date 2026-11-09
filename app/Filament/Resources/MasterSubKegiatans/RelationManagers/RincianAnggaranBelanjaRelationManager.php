<?php

namespace App\Filament\Resources\MasterSubKegiatans\RelationManagers;

use App\Enums\SumberDana;
use App\Models\KodeRekening;
use App\Models\MasterBarang;
use App\Models\MasterSubKegiatan;
use App\Models\RincianAnggaranBelanja;
use App\Models\Satuan;
use App\Support\FormatHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

class RincianAnggaranBelanjaRelationManager extends RelationManager
{
    protected static string $relationship = 'rincianAnggarans';

    protected static ?string $title = 'Rincian Belanja (RKA)';

    protected static ?string $modelLabel = 'Item Belanja';

    protected static ?string $pluralModelLabel = 'Rincian Belanja';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rekening & Komponen Belanja')
                    ->description('Kelola rekening belanja dan master barang')
                    ->schema([
                        Select::make('sumber_dana')
                            ->label('Sumber Pendanaan')
                            ->options(SumberDana::class)
                            ->default(SumberDana::DAU->value)
                            ->required(),

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
                            }),

                        Select::make('master_barang_id')
                            ->label('Komponen Belanja / Barang')
                            ->placeholder(fn (Get $get): string => filled($get('kode_rekening_id')) ? 'Pilih komponen barang...' : 'Pilih Sub Rincian Obyek terlebih dahulu')
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
                                TextInput::make('harga_satuan')
                                    ->label('Harga Satuan Standar (Rp)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0),
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
                                        $harga = (float) $barang->harga_satuan;
                                        $set('harga_satuan', $harga);
                                        $vol = max(1, (float) ($get('volume') ?: 1));
                                        $set('volume', $vol);
                                        $set('pagu_total', number_format($vol * $harga, 0, ',', '.'));
                                    }
                                }
                            })
                            ->required(),

                        TextInput::make('nama_item')
                            ->label('Nama Item / Uraian RKA')
                            ->required(),

                        TextInput::make('spesifikasi')
                            ->label('Spesifikasi Teknis (Opsional)')
                            ->placeholder('Contoh: Warna Putih, 75 GSM, Ukuran Folio')
                            ->maxLength(255),
                    ]),

                Section::make('Kalkulasi Pagu & Catatan')
                    ->description('Volume, harga satuan standar, dan pagu anggaran')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('volume')
                                    ->label('Volume / Koefisien')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $vol = (float) ($state ?: 0);
                                        $harga = (float) ($get('harga_satuan') ?: 0);
                                        $set('pagu_total', number_format($vol * $harga, 0, ',', '.'));
                                    }),

                                TextInput::make('satuan')
                                    ->label('Satuan')
                                    ->placeholder('Rim / Kotak / Lembar')
                                    ->required(),
                            ]),

                        TextInput::make('harga_satuan')
                            ->label('Harga Standar Satuan')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $vol = (float) ($get('volume') ?: 0);
                                $harga = (float) ($state ?: 0);
                                $set('pagu_total', number_format($vol * $harga, 0, ',', '.'));
                            }),

                        TextInput::make('pagu_total')
                            ->label('Total Pagu Anggaran')
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($state, ?RincianAnggaranBelanja $record) => $record ? number_format((float) $record->pagu_total, 0, ',', '.') : $state),

                        Textarea::make('keterangan')
                            ->label('Catatan Tambahan (Opsional)')
                            ->rows(3),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_item')
            ->recordActionsPosition(RecordActionsPosition::BeforeColumns)
            ->recordActionsColumnLabel('Aksi')
            ->stickyableColumns()
            ->columns([
                TextColumn::make('nama_item')
                    ->label('Uraian Barang / Belanja')
                    ->description(fn (RincianAnggaranBelanja $r): ?string => $r->spesifikasi)
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap()
                    ->extraAttributes(['style' => 'min-width: 280px;'], merge: true)
                    ->extraHeaderAttributes(['style' => 'min-width: 280px;'], merge: true)
                    ->sticky(),

                TextColumn::make('kodeRekening.uraian')
                    ->label('Sub Rincian Obyek')
                    ->description(fn (RincianAnggaranBelanja $r): ?string => $r->kodeRekening?->kode_lengkap)
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->wrap()
                    ->extraAttributes(['style' => 'min-width: 240px;'])
                    ->extraHeaderAttributes(['style' => 'min-width: 240px;']),

                TextColumn::make('volume')
                    ->label('Pagu Volume')
                    ->state(fn (RincianAnggaranBelanja $r): string => number_format((float) $r->volume, 0, ',', '.') . ' ' . $r->satuan)
                    ->color('gray')
                    ->alignCenter(),

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

                TextColumn::make('sisa_anggaran')
                    ->label('Sisa Anggaran')
                    ->state(fn (RincianAnggaranBelanja $r): string => FormatHelper::rupiah($r->sisa_anggaran))
                    ->color(fn (RincianAnggaranBelanja $r): string => $r->sisa_anggaran > 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->alignEnd(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Item Belanja RKA')
                    ->icon('heroicon-o-plus')
                    ->slideOver()
                    ->modalWidth('5xl')
                    ->mutateFormDataUsing(function (array $data): array {
                        /** @var MasterSubKegiatan $owner */
                        $owner = $this->getOwnerRecord();
                        $data['sub_kegiatan_id'] = $owner->id;
                        $data['tahun_anggaran'] = $owner->tahun_anggaran ?: (int) date('Y');
                        $data['bidang_id'] = $owner->kegiatan?->bidang_id;
                        $data['volume_realisasi'] = 0;
                        $data['nilai_realisasi'] = 0;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('5xl'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

