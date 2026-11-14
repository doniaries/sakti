<?php

namespace App\Filament\Resources\HariLiburs\Pages;

use App\Filament\Resources\HariLiburs\HariLiburResource;
use App\Services\HariLiburService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListHariLiburs extends ListRecords
{
    protected static string $resource = HariLiburResource::class;

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua'),
            'nasional' => Tab::make('Libur Nasional')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('jenis', 'nasional')),
            'cuti_bersama' => Tab::make('Cuti Bersama')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('jenis', 'cuti_bersama')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Libur Manual')
                ->icon('heroicon-o-plus'),

            Action::make('tarik_libur_api')
                ->label('Tarik Libur Nasional (API)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->modalHeading('Tarik Hari Libur Nasional & Cuti Bersama')
                ->modalDescription('Sistem akan menyinkronkan data hari libur nasional dan cuti bersama resmi dari API publik / dataset SKB 3 Menteri dan menyimpannya secara permanen ke database lokal Anda.')
                ->modalSubmitActionLabel('Mulai Tarik Data')
                ->form([
                    TextInput::make('tahun')
                        ->label('Tahun Libur')
                        ->numeric()
                        ->default(now()->year)
                        ->required(),
                ])
                ->action(function (array $data, HariLiburService $service) {
                    $tahun = (int) ($data['tahun'] ?? now()->year);
                    $result = $service->syncFromApi($tahun);

                    if ($result['success']) {
                        Notification::make()
                            ->title('Sinkronisasi Berhasil!')
                            ->body($result['message'])
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Sinkronisasi Gagal')
                            ->body($result['message'])
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
