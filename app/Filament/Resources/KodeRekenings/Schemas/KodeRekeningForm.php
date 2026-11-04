<?php

namespace App\Filament\Resources\KodeRekenings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class KodeRekeningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Rekening Belanja')
                    ->description('Masukkan kode rekening dan uraian rekening belanja sesuai standar SIPD / Permendagri.')
                    ->schema([
                        TextInput::make('kode_lengkap')
                            ->label('Kode Rekening Belanja')
                            ->placeholder('Contoh: 5.1.02.01.001.00024')
                            ->mask(RawJs::make(<<<'JS'
                                $input.startsWith('5') ? '9.9.99.99.999.99999' : '9.9.9.99.99.99.999'
                            JS))
                            ->helperText('Format titik otomatis: 5.1.02.01.001.00024 (titik disisipkan otomatis saat mengetik angka)')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('uraian')
                            ->label('Uraian Rekening Belanja')
                            ->placeholder('Contoh: Belanja Alat/Bahan untuk Kegiatan Kantor-Alat Tulis Kantor')
                            ->required(),
                    ]),

                Section::make('Rincian Segmen Akun (Otomatis Terisi)')
                    ->description('Segmen terisi otomatis saat kode rekening disimpan, atau dapat disesuaikan jika perlu.')
                    ->collapsed()
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('akun')->label('Akun')->default('5'),
                            TextInput::make('kelompok')->label('Kelompok')->default('1'),
                            TextInput::make('jenis')->label('Jenis')->default('02'),
                            TextInput::make('objek')->label('Objek')->default('01'),
                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('rincian_objek')->label('Rincian Objek')->default('001'),
                            TextInput::make('sub_rincian_objek')->label('Sub Rincian')->default('00001'),
                            TextInput::make('sub_sub_rincian_objek')->label('Sub-Sub Rincian')->default('000'),
                        ]),
                    ]),
            ]);
    }
}
