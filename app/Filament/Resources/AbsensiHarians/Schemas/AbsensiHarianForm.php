<?php

namespace App\Filament\Resources\AbsensiHarians\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AbsensiHarianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informasi Absensi Harian')
                    ->schema([
                        Select::make('user_id')
                            ->label('Nama Pegawai')
                            ->options(
                                User::orderBy('name', 'asc')
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->default(fn() => Auth::id())
                            ->searchable()
                            ->placeholder('Semua Pegawai'),
                        DatePicker::make('tanggal')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->required(),
                        TimePicker::make('jam_masuk')
                            ->displayFormat('H:i:s')
                            ->visible(fn(Get $get) => in_array($get('status'), ['hadir', 'terlambat', 'dinas_luar'])),
                        TimePicker::make('jam_pulang')
                            ->displayFormat('H:i:s')
                            ->visible(fn(Get $get) => in_array($get('status'), ['hadir', 'terlambat', 'dinas_luar'])),
                        Select::make('status')
                            ->options(\App\Enums\StatusKehadiran::class)
                            ->default(\App\Enums\StatusKehadiran::HADIR)
                            ->required()
                            ->live(),
                        Select::make('status_apel')
                            ->label('Status Apel')
                            ->options(\App\Enums\StatusApel::class),
                        Textarea::make('keterangan')
                            ->columnSpanFull(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Sumber Absensi')
                    ->schema([
                        \Filament\Forms\Components\Toggle::make('is_absen_mesin')
                            ->label('Dari Mesin'),
                        \Filament\Forms\Components\Toggle::make('is_absen_mobile')
                            ->label('Dari Mobile'),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Bukti & Dokumen')
                    ->schema([
                        \Filament\Forms\Components\FileUpload::make('foto_masuk')
                            ->label('Foto Masuk')
                            ->image()
                            ->directory('absensi-mobile')
                            ->maxSize(2048)
                            ->visible(fn(Get $get) => in_array($get('status'), ['hadir', 'terlambat', 'dinas_luar'])),
                        \Filament\Forms\Components\FileUpload::make('foto_pulang')
                            ->label('Foto Pulang')
                            ->image()
                            ->directory('absensi-mobile')
                            ->maxSize(2048)
                            ->visible(fn(Get $get) => in_array($get('status'), ['hadir', 'terlambat', 'dinas_luar'])),
                        FileUpload::make('bukti')
                            ->label('Bukti Izin / Sakit')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->directory('absensi-bukti')
                            ->maxSize(2048)
                            ->visible(fn(Get $get) => in_array($get('status'), ['izin', 'sakit', 'cuti'])),
                    ])->columns(2),
            ]);
    }
}
