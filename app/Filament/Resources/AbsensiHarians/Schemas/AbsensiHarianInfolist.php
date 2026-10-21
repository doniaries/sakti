<?php

namespace App\Filament\Resources\AbsensiHarians\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Facades\Storage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AbsensiHarianInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->schema([
                        TextEntry::make('user.name')->label('Pegawai'),
                        TextEntry::make('tanggal')->date('d F Y'),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('status_apel')
                            ->badge(),
                        TextEntry::make('keterangan'),
                    ])->columns(2),

                Section::make('Detail Kehadiran')
                    ->schema([
                        TextEntry::make('jam_masuk')
                            ->label('Jam Masuk')
                            ->time('H:i')
                            ->visible(fn($record) => in_array($record->status, ['hadir', 'terlambat', 'dinas_luar'])),
                        ImageEntry::make('foto_masuk')
                            ->label('Bukti Masuk (Mobile)')
                            ->visible(fn($record) => in_array($record->status, ['hadir', 'terlambat', 'dinas_luar'])),

                        TextEntry::make('jam_pulang')
                            ->label('Jam Pulang')
                            ->time('H:i')
                            ->visible(fn($record) => in_array($record->status, ['hadir', 'terlambat', 'dinas_luar'])),
                        ImageEntry::make('foto_pulang')
                            ->label('Bukti Pulang (Mobile)')
                            ->visible(fn($record) => in_array($record->status, ['hadir', 'terlambat', 'dinas_luar'])),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => in_array($record->status, ['hadir', 'terlambat', 'dinas_luar'])),

                Section::make('Bukti Surat Izin / Sakit')
                    ->schema([
                        TextEntry::make('bukti')
                            ->label('Surat Keterangan')
                            ->visible(fn($record) => in_array($record->status, ['izin', 'sakit', 'cuti']))
                            ->formatStateUsing(function ($state) {
                                if (!$state) {
                                    return '<img src="' . url('/images/placeholder.png') . '" style="max-width: 100%; border-radius: 8px;" alt="Tidak ada bukti" />';
                                }
                                
                                $url = Storage::url($state);
                                $ext = pathinfo($state, PATHINFO_EXTENSION);
                                
                                if (in_array(strtolower($ext), ['pdf'])) {
                                    return '<embed src="' . $url . '" type="application/pdf" width="100%" height="600px" style="border-radius: 8px; border: 1px solid #e5e7eb;" />';
                                }
                                
                                return '<img src="' . $url . '" style="max-width: 100%; border-radius: 8px;" alt="Bukti" />';
                            })
                            ->html(),
                    ])
                    ->visible(fn($record) => in_array($record->status, ['izin', 'sakit', 'cuti'])),
            ]);
    }
}
