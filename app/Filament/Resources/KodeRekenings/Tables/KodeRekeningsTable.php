<?php

namespace App\Filament\Resources\KodeRekenings\Tables;

use App\Models\KodeRekening;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class KodeRekeningsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('level')
                    ->label('Tingkat')
                    ->badge()
                    ->state(fn (KodeRekening $record): string => $record->nama_level)
                    ->color(fn (KodeRekening $record): string => $record->warna_level)
                    ->sortable(query: function (Builder $query, string $direction) {
                        return $query->orderByRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', '')) + 1) {$direction}");
                    }),

                TextColumn::make('kode_lengkap')
                    ->label('Kode Rekening')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->weight(fn (KodeRekening $record): string => $record->level <= 2 ? 'bold' : ($record->level <= 4 ? 'semibold' : 'normal'))
                    ->copyable()
                    ->copyMessage('Kode rekening disalin')
                    ->copyMessageDuration(1500),

                TextColumn::make('uraian')
                    ->label('Uraian Rekening Belanja')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->formatStateUsing(function (string $state, KodeRekening $record): HtmlString {
                        $level = $record->level;
                        $paddingPx = max(0, ($level - 1) * 18);
                        $isInduk = $level <= 2;
                        $iconPrefix = match ($level) {
                            1 => '👑 ',
                            2 => '📁 ',
                            3 => '📂 ',
                            4 => '▸ ',
                            5 => '↳ ',
                            default => '<span class="text-emerald-500 font-bold">└─ </span>',
                        };

                        $textClass = match ($level) {
                            1 => 'font-extrabold text-sm text-gray-900 dark:text-white uppercase tracking-wider',
                            2 => 'font-bold text-xs text-amber-700 dark:text-amber-400 uppercase',
                            3 => 'font-semibold text-xs text-blue-700 dark:text-blue-300',
                            4 => 'font-medium text-xs text-indigo-700 dark:text-indigo-300',
                            5 => 'font-normal text-xs text-gray-700 dark:text-zinc-300',
                            default => 'font-normal text-xs text-gray-900 dark:text-zinc-100',
                        };

                        $uraianText = $isInduk ? mb_strtoupper($state, 'UTF-8') : htmlspecialchars($state, ENT_QUOTES, 'UTF-8');

                        return new HtmlString(
                            "<div style=\"padding-left: {$paddingPx}px;\" class=\"flex items-center gap-1.5\">" .
                                "<span class=\"text-[11px] opacity-70\">{$iconPrefix}</span>" .
                                "<span class=\"{$textClass}\">{$uraianText}</span>" .
                            "</div>"
                        );
                    }),

                TextColumn::make('status_rka')
                    ->label('Peruntukan')
                    ->badge()
                    ->state(fn (KodeRekening $record): string => $record->level >= 5 ? 'Item Belanja RKA' : 'Kategori Induk')
                    ->color(fn (KodeRekening $record): string => $record->level >= 5 ? 'success' : 'gray')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('kode_lengkap', 'asc')
            ->filters([
                SelectFilter::make('tingkat')
                    ->label('Filter Berdasarkan Tingkat Rekening')
                    ->options([
                        '1' => 'Level 1 - Akun Utama (5 - Belanja Daerah)',
                        '2' => 'Level 2 - Kelompok (5.1 Belanja Operasi, 5.2 Modal, dst)',
                        '3' => 'Level 3 - Jenis (5.1.01 Pegawai, 5.1.02 Barang & Jasa, dst)',
                        '4' => 'Level 4 - Objek Belanja',
                        '5' => 'Level 5 - Rincian Objek',
                        '6' => 'Level 6 - Sub Rincian (Item Belanja Siap Pakai di RKA/SPJ)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (filled($data['value'])) {
                            $level = (int) $data['value'];
                            $dots = $level - 1;
                            $query->whereRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', ''))) = ?", [$dots]);
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, KodeRekening $record) {
                        $spjCount = $record->detailPengajuanSpjs()->count();
                        $rkaCount = \App\Models\RincianAnggaranBelanja::where('kode_rekening_id', $record->id)->count();
                        $mbCount = $record->masterBarangs()->count();

                        if ($spjCount > 0 || $rkaCount > 0 || $mbCount > 0) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Gagal Menghapus Rekening')
                                ->body("Kode rekening {$record->kode_lengkap} tidak dapat dihapus karena masih digunakan oleh {$spjCount} transaksi SPJ, {$rkaCount} item RKA, dan {$mbCount} barang persediaan.")
                                ->persistent()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, \Illuminate\Support\Collection $records) {
                            $blocked = [];
                            foreach ($records as $record) {
                                $spjCount = $record->detailPengajuanSpjs()->count();
                                $rkaCount = \App\Models\RincianAnggaranBelanja::where('kode_rekening_id', $record->id)->count();
                                $mbCount = $record->masterBarangs()->count();

                                if ($spjCount > 0 || $rkaCount > 0 || $mbCount > 0) {
                                    $blocked[] = $record->kode_lengkap;
                                }
                            }

                            if (! empty($blocked)) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('Gagal Menghapus Beberapa Rekening')
                                    ->body('Rekening (' . implode(', ', $blocked) . ') tidak dapat dihapus karena masih digunakan pada data transaksi SPJ, RKA, atau barang persediaan.')
                                    ->persistent()
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }
}
