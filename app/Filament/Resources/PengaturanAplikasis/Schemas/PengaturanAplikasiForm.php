<?php

namespace App\Filament\Resources\PengaturanAplikasis\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PengaturanAplikasiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Aplikasi & Logo')
                    ->description('Konfigurasi nama sistem, logo instansi, dan sub-judul yang tampil pada dashboard mobile maupun cetak laporan.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nama_aplikasi')
                                    ->label('Nama Aplikasi')
                                    ->placeholder('Contoh: ABSENSI LHK')
                                    ->required()
                                    ->default('ABSENSI LHK')
                                    ->maxLength(100),

                                TextInput::make('sub_nama')
                                    ->label('Sub-Judul / Instansi')
                                    ->placeholder('Contoh: Dinas Lingkungan Hidup')
                                    ->default('Dinas Lingkungan Hidup')
                                    ->maxLength(150),
                            ]),

                        FileUpload::make('logo')
                            ->label('Logo Aplikasi / Instansi')
                            ->helperText('Format file gambar (PNG, JPG, SVG). Disarankan rasio kotak / persegi.')
                            ->image()
                            ->directory('aplikasi')
                            ->imageEditor()
                            ->maxSize(2048),
                    ]),

                Section::make('Informasi Versi & Pengembang')
                    ->description('Rincian versi sistem, pihak pembuat, serta kontak bantuan resmi.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('versi_aplikasi')
                                    ->label('Versi Aplikasi')
                                    ->placeholder('Contoh: v1.0.0')
                                    ->required()
                                    ->default('v1.0.0'),

                                TextInput::make('pembuat_aplikasi')
                                    ->label('Pembuat / Developer Aplikasi')
                                    ->placeholder('Contoh: Diskominfotik')
                                    ->default('Diskominfotik'),

                                TextInput::make('kontak_bantuan')
                                    ->label('Kontak / Email Bantuan')
                                    ->placeholder('Contoh: helpdesk@lhk.go.id'),
                            ]),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi / Catatan Sistem')
                            ->rows(3)
                            ->placeholder('Sistem Presensi & Laporan Harian Kinerja Pegawai')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
