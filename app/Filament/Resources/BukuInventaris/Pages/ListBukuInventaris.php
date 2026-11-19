<?php

declare(strict_types=1);

namespace App\Filament\Resources\BukuInventaris\Pages;

use App\Filament\Resources\BukuInventaris\BukuInventarisResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBukuInventaris extends ListRecords
{
    protected static string $resource = BukuInventarisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pratinjau_html')
                ->label('Pratinjau Laporan')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn (): string => route('admin.buku-inventaris.cetak', ['preview' => 1]))
                ->openUrlInNewTab(),

            Action::make('cetak_pdf')
                ->label('Cetak Buku Inventaris (PDF)')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->url(fn (): string => route('admin.buku-inventaris.cetak'))
                ->openUrlInNewTab(),

            CreateAction::make()
                ->label('Tambah Aset')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }
}
