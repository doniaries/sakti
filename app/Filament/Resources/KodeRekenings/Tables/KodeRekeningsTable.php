<?php

namespace App\Filament\Resources\KodeRekenings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KodeRekeningsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_lengkap')
                    ->label('Kode Rekening')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('uraian')
                    ->label('Uraian Rekening')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->formatStateUsing(fn (?string $state): string => mb_strtoupper((string) $state, 'UTF-8')),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, \App\Models\KodeRekening $record) {
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
