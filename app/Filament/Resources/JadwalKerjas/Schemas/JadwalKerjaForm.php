<?php

namespace App\Filament\Resources\JadwalKerjas\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JadwalKerjaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Jam Kerja & Shift')
                    ->description('Konfigurasi shift kerja harian pegawai (Reguler, Shift Khusus, atau Jadwal Ramadhan).')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nama')
                                    ->label('Nama Jadwal / Shift')
                                    ->placeholder('Contoh: Jadwal Reguler Dinas / Shift Ramadhan')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('kode')
                                    ->label('Kode Unik')
                                    ->placeholder('Contoh: reguler, ramadhan, piket')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50),
                            ]),

                        Section::make('Jendela Waktu Presensi Masuk (Pagi)')
                            ->description('Atur jendela jam masuk, toleransi keterlambatan, dan batas tutup masuk.')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TimePicker::make('buka_absen_masuk')
                                            ->label('Buka Absen Masuk')
                                            ->helperText('Jam paling awal pegawai bisa tap masuk.')
                                            ->seconds(false)
                                            ->default('06:00')
                                            ->required(),

                                        TimePicker::make('jam_masuk_senin_kamis')
                                            ->label('Jam Masuk (Senin - Kamis)')
                                            ->helperText('Jam resmi mulai kerja Senin-Kamis.')
                                            ->seconds(false)
                                            ->default('07:30')
                                            ->required(),

                                        TimePicker::make('jam_masuk_jumat')
                                            ->label('Jam Masuk (Jumat)')
                                            ->helperText('Jam resmi mulai kerja hari Jumat.')
                                            ->seconds(false)
                                            ->default('07:30')
                                            ->required(),

                                        TimePicker::make('tutup_absen_masuk')
                                            ->label('Tutup Absen Masuk')
                                            ->helperText('Batas akhir tap masuk (lewat jam ini = alpa).')
                                            ->seconds(false)
                                            ->default('09:00')
                                            ->required(),
                                    ]),

                                TextInput::make('toleransi_terlambat_menit')
                                    ->label('Toleransi Keterlambatan')
                                    ->helperText('Rentang menit setelah jam masuk resmi yang masih dihitung tepat waktu (misal 15 menit: 07:30 s.d 07:45 tetap Hadir).')
                                    ->numeric()
                                    ->suffix('menit')
                                    ->default(15)
                                    ->required(),
                            ]),

                        Section::make('Jendela Waktu Presensi Pulang (Sore/Malam)')
                            ->description('Atur jam pulang resmi (Senin-Kamis vs Jumat) dan batas tutup absen pulang.')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TimePicker::make('jam_pulang_senin_kamis')
                                            ->label('Jam Pulang (Senin - Kamis)')
                                            ->helperText('Mulai buka absen pulang Senin-Kamis.')
                                            ->seconds(false)
                                            ->default('16:00')
                                            ->required(),

                                        TimePicker::make('jam_pulang_jumat')
                                            ->label('Jam Pulang (Jumat)')
                                            ->helperText('Mulai buka absen pulang hari Jumat.')
                                            ->seconds(false)
                                            ->default('16:30')
                                            ->required(),

                                        TimePicker::make('tutup_absen_pulang')
                                            ->label('Tutup Absen Pulang')
                                            ->helperText('Batas akhir tap pulang malam hari.')
                                            ->seconds(false)
                                            ->default('21:00')
                                            ->required(),
                                    ]),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_default')
                                    ->label('Jadwal Default Utama Instansi')
                                    ->helperText('Otomatis berlaku untuk seluruh pegawai yang tidak diset shift khusus.')
                                    ->default(false),

                                Toggle::make('is_aktif')
                                    ->label('Status Aktif')
                                    ->default(true),
                            ]),

                        Textarea::make('keterangan')
                            ->label('Keterangan / Dasar Hukum')
                            ->placeholder('Contoh: Sesuai Perbup No. XX tentang Jam Kerja ASN.')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
