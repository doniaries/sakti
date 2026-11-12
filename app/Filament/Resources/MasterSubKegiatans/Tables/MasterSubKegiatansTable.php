<?php

namespace App\Filament\Resources\MasterSubKegiatans\Tables;

use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Models\Bidang;
use App\Models\MasterKegiatan;
use App\Models\MasterSubKegiatan;
use App\Models\User;
use App\Support\FormatHelper;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterSubKegiatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordActionsPosition(RecordActionsPosition::BeforeColumns)
            ->recordActionsColumnLabel('Aksi')
            ->stickyableColumns()
            ->recordUrl(fn(MasterSubKegiatan $record): string => MasterSubKegiatanResource::getUrl('edit', ['record' => $record]))
            ->modifyQueryUsing(function (Builder $query): Builder {
                $tahunAktif = \App\Models\PengaturanAplikasi::getTahunAnggaranAktif();

                $query->with(['kegiatan.bidang', 'kegiatan.program', 'jf'])
                    ->withSum('rincianAnggarans as total_pagu', 'pagu_total')
                    ->withSum('rincianAnggarans as total_realisasi', 'nilai_realisasi')
                    ->withCount('rincianAnggarans')
                    ->where('tahun_anggaran', $tahunAktif);

                return $query;
            })
            ->columns([
                TextColumn::make('nama_sub_kegiatan')
                    ->label('Sub Kegiatan')
                    ->html()
                    ->formatStateUsing(function ($state, MasterSubKegiatan $record): string {
                        $program = $record->kegiatan?->program;
                        $kegiatan = $record->kegiatan;

                        $kodeProgram = $program?->kode_program ? "[{$program->kode_program}] " : '';
                        $namaProgram = mb_strtoupper($program?->nama_program ?? 'PROGRAM BELUM DIATUR', 'UTF-8');

                        $kodeKegiatan = $kegiatan?->kode_kegiatan ? "[{$kegiatan->kode_kegiatan}] " : '';
                        $namaKegiatan = mb_strtoupper($kegiatan?->nama_kegiatan ?? 'KEGIATAN BELUM DIATUR', 'UTF-8');

                        $kodeSub = $record->kode_sub_kegiatan ? "[{$record->kode_sub_kegiatan}] " : '';
                        $namaSub = mb_strtolower((string) $state, 'UTF-8');

                        return <<<HTML
                        <div class="flex flex-col gap-1 py-1 text-left leading-normal">
                            <div class="font-bold text-xs text-primary-700 dark:text-primary-400 uppercase tracking-tight">
                                {$kodeProgram}{$namaProgram}
                            </div>
                            <div class="font-normal text-xs text-slate-700 dark:text-slate-300 uppercase tracking-tight">
                                {$kodeKegiatan}{$namaKegiatan}
                            </div>
                            <div class="font-normal text-sm text-slate-900 dark:text-slate-100 lowercase">
                                {$kodeSub}{$namaSub}
                            </div>
                        </div>
                        HTML;
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search) {
                            $q->where('nama_sub_kegiatan', 'like', "%{$search}%")
                                ->orWhere('kode_sub_kegiatan', 'like', "%{$search}%")
                                ->orWhereHas('kegiatan', function ($qk) use ($search) {
                                    $qk->where('nama_kegiatan', 'like', "%{$search}%")
                                        ->orWhere('kode_kegiatan', 'like', "%{$search}%")
                                        ->orWhereHas('program', function ($qp) use ($search) {
                                            $qp->where('nama_program', 'like', "%{$search}%")
                                                ->orWhere('kode_program', 'like', "%{$search}%");
                                        });
                                });
                        });
                    })
                    ->sortable()
                    ->wrap()
                    ->extraAttributes(['style' => 'min-width: 380px;'], merge: true)
                    ->extraHeaderAttributes(['style' => 'min-width: 380px;'], merge: true)
                    ->sticky(),

                TextColumn::make('jf.name')
                    ->label('Penanggung Jawab (JF)')
                    ->placeholder('Belum Ditugaskan')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger')
                    ->icon(fn($state) => $state ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->action(
                        Action::make('ganti_jf_inline')
                            ->modalHeading(fn(MasterSubKegiatan $record): string => "Penugasan JF: {$record->nama_sub_kegiatan}")
                            ->modalDescription('Pilih Pejabat Fungsional (JF) yang bertanggung jawab atas Sub Kegiatan ini.')
                            ->visible(fn() => auth()->user()?->hasAnyRole(['pptk', 'super_admin', 'admin', 'pa']))
                            ->form([
                                Select::make('jf_id')
                                    ->label('Pejabat Fungsional (JF)')
                                    ->options(function (MasterSubKegiatan $record): array {
                                        $targetBidangId = $record->kegiatan?->bidang_id;

                                        return User::query()
                                            ->where('aktif', true)
                                            ->when($targetBidangId, fn($q) => $q->where('bidang_id', $targetBidangId))
                                            ->whereNotNull('eselon')
                                            ->where('eselon', '!=', \App\Enums\Eselon::NON_ESELON)
                                            ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['pa', 'ppk']))
                                            ->orderBy('eselon')
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(function (User $u) {
                                                $eselonVal = $u->eselon instanceof \App\Enums\Eselon ? $u->eselon->value : (string) $u->eselon;
                                                return [$u->id => "{$u->name} (Eselon {$eselonVal})"];
                                            })
                                            ->all();
                                    })
                                    ->default(fn(MasterSubKegiatan $record) => $record->jf_id)
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Hanya menampilkan pejabat bereselon di bidang sub kegiatan ini.')
                                    ->required(),
                            ])
                            ->action(function (MasterSubKegiatan $record, array $data): void {
                                $record->update(['jf_id' => $data['jf_id']]);
                                Notification::make()
                                    ->title('Penugasan JF Berhasil Disimpan')
                                    ->success()
                                    ->send();
                            })
                    ),

                TextColumn::make('total_pagu')
                    ->label('Pagu Anggaran')
                    ->formatStateUsing(fn($state) => FormatHelper::rupiah((float) $state))
                    ->weight('bold')
                    ->alignEnd()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Pagu')
                            ->formatStateUsing(fn($state) => FormatHelper::rupiah((float) $state))
                    ),

                TextColumn::make('total_realisasi')
                    ->label('Realisasi SPJ')
                    ->formatStateUsing(fn($state) => FormatHelper::rupiah((float) $state))
                    ->alignEnd()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Realisasi')
                            ->formatStateUsing(fn($state) => FormatHelper::rupiah((float) $state))
                    ),

                TextColumn::make('sisa_pagu')
                    ->label('Sisa Pagu')
                    ->state(fn(MasterSubKegiatan $record): string => FormatHelper::rupiah($record->sisa_pagu))
                    ->color(fn(MasterSubKegiatan $record): string => $record->sisa_pagu > 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->alignEnd(),

                TextColumn::make('rincian_anggarans_count')
                    ->label('Item Belanja')
                    ->badge()
                    ->color(fn($state) => (int) $state > 0 ? 'info' : 'gray')
                    ->formatStateUsing(fn($state) => "{$state} Item")
                    ->alignCenter(),

                TextColumn::make('is_locked')
                    ->label('Status RKA')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Terkunci (DPA)' : 'Terbuka (Revisi)')
                    ->color(fn (bool $state): string => $state ? 'danger' : 'success')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('tahun_anggaran')
                    ->label('Tahun')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('kegiatan.bidang.nama')
                    ->label('Bidang')
                    ->badge()
                    ->color(fn(?string $state): string => Bidang::badgeColor($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kegiatan_id')
                    ->label('Kegiatan Induk')
                    ->relationship('kegiatan', 'nama_kegiatan')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('penugasan_jf')
                    ->label('Status Penugasan JF')
                    ->placeholder('Semua Sub Kegiatan')
                    ->trueLabel('Sudah Ditugaskan')
                    ->falseLabel('Belum Ditugaskan (Perlu JF)')
                    ->queries(
                        true: fn(Builder $q) => $q->whereNotNull('jf_id'),
                        false: fn(Builder $q) => $q->whereNull('jf_id'),
                    ),

                TernaryFilter::make('is_locked')
                    ->label('Status Kunci RKA')
                    ->placeholder('Semua Status')
                    ->trueLabel('Terkunci (DPA)')
                    ->falseLabel('Terbuka (Revisi)'),

                SelectFilter::make('jf_id')
                    ->label('Filter JF')
                    ->options(function () {
                        /** @var \App\Models\User|null $user */
                        $user = auth()->user();
                        $userBidangId = $user?->bidang_id ?: ($user ? \App\Models\Bidang::where('kepala_id', $user->id)->value('id') : null);

                        return User::query()
                            ->where('aktif', true)
                            ->when($userBidangId, fn($q) => $q->where('bidang_id', $userBidangId))
                            ->whereHas('roles', fn($q) => $q->where('name', 'jf'))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->placeholder('Semua JF'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('kelola_rka')
                    ->label('Rincian Belanja RKA')
                    ->icon('heroicon-o-table-cells')
                    ->color('primary')
                    ->url(fn(MasterSubKegiatan $record): string => MasterSubKegiatanResource::getUrl('edit', ['record' => $record])),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                Action::make('select_tahun_anggaran')
                    ->view('filament.tables.toolbar.tahun-anggaran-select'),

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
