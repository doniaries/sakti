<?php

namespace App\Filament\Resources\KodeRekenings\Tables;

use App\Models\KodeRekening;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->color(fn (KodeRekening $record): string => match ($record->level) {
                        1 => 'danger',
                        2 => 'warning',
                        3 => 'info',
                        4 => 'primary',
                        5 => 'gray',
                        default => 'success',
                    })
                    ->sortable(query: function (Builder $query, string $direction) {
                        return $query->orderByRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', '')) + 1) {$direction}");
                    }),

                TextColumn::make('kode_lengkap')
                    ->label('Kode Rekening')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->weight(fn (KodeRekening $record): FontWeight => $record->level <= 2 ? FontWeight::Bold : ($record->level <= 4 ? FontWeight::SemiBold : FontWeight::Normal))
                    ->copyable()
                    ->copyMessage('Kode rekening disalin')
                    ->copyMessageDuration(1500),

                TextColumn::make('uraian')
                    ->label('Uraian Rekening Belanja')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight(fn (KodeRekening $record): FontWeight => $record->level <= 2 ? FontWeight::Bold : ($record->level <= 4 ? FontWeight::SemiBold : FontWeight::Normal))
                    ->formatStateUsing(fn (?string $state, KodeRekening $record): string => $record->level <= 2 ? mb_strtoupper((string) $state, 'UTF-8') : (string) $state),

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
                            $dots = (int) $data['value'] - 1;
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
