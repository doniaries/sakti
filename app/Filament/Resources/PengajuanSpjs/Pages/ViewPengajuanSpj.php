<?php

namespace App\Filament\Resources\PengajuanSpjs\Pages;

use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPengajuanSpj extends ViewRecord
{
    protected static string $resource = PengajuanSpjResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak')
                ->label('Cetak Dokumen SPJ (PDF)')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn (): string => route('admin.pengajuan-spj.cetak', $this->record))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
