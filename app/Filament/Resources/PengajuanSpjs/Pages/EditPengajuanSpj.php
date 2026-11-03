<?php

namespace App\Filament\Resources\PengajuanSpjs\Pages;

use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanSpj extends EditRecord
{
    protected static string $resource = PengajuanSpjResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak')
                ->label('Cetak Dokumen SPJ')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn (): string => route('admin.pengajuan-spj.cetak', $this->record))
                ->openUrlInNewTab(),
            DeleteAction::make()
                ->visible(fn (): bool => in_array($this->record->status, ['draft', 'ditolak'])),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

