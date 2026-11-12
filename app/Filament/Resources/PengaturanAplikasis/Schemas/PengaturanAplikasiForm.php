<?php

namespace App\Filament\Resources\PengaturanAplikasis\Schemas;

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
                Section::make('Identitas Aplikasi SAKTI')
                    ->description('Konfigurasi nama sistem aplikasi, sub-judul kepanjangan, dan ringkasan sistem.')
                    ->icon('heroicon-o-sparkles')
                    ->compact()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3])
                            ->schema([
                                TextInput::make('nama_aplikasi')
                                    ->label('Nama Aplikasi / Sistem')
                                    ->placeholder('Contoh: SAKTI')
                                    ->required()
                                    ->default('SAKTI')
                                    ->maxLength(100),

                                TextInput::make('sub_nama')
                                    ->label('Sub-Judul (Kepanjangan SAKTI)')
                                    ->placeholder('Contoh: Sistem Administrasi Kinerja Terintegrasi')
                                    ->required()
                                    ->default('Sistem Administrasi Kinerja Terintegrasi')
                                    ->helperText('Kepanjangan singkatan SAKTI yang tampil di header dan portal.')
                                    ->columnSpan(['md' => 2])
                                    ->maxLength(200),
                            ]),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi / Ringkasan Sistem')
                            ->rows(2)
                            ->placeholder('Sistem Administrasi Kinerja Terintegrasi')
                            ->columnSpanFull(),
                    ]),

                Section::make('Informasi Sistem & Dukungan Teknis')
                    ->description('Informasi versi aplikasi dan kontak resmi pengembang.')
                    ->icon('heroicon-o-information-circle')
                    ->compact()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3])
                            ->schema([
                                TextInput::make('versi_aplikasi')
                                    ->label('Versi Aplikasi')
                                    ->placeholder('Contoh: v1.0.0')
                                    ->prefixIcon('heroicon-m-tag')
                                    ->required()
                                    ->default('v1.0.0'),

                                TextInput::make('pembuat_aplikasi')
                                    ->label('Pengembang Aplikasi')
                                    ->placeholder('Contoh: Diskominfotik')
                                    ->prefixIcon('heroicon-m-user-group')
                                    ->default('Diskominfotik'),

                                TextInput::make('kontak_bantuan')
                                    ->label('Kontak / Email Bantuan')
                                    ->placeholder('Contoh: helpdesk@lhk.go.id')
                                    ->prefixIcon('heroicon-m-envelope'),
                            ]),
                    ]),
            ]);
    }
}
