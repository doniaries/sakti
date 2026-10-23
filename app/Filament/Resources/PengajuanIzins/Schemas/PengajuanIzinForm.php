<?php

namespace App\Filament\Resources\PengajuanIzins\Schemas;

use App\Models\JenisIzin;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PengajuanIzinForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pengajuan Izin, Sakit & Cuti')
                    ->description('Isi data pengajuan ketidakhadiran kerja atau WFH.')
                    ->schema([
                        Select::make('user_id')
                            ->label('Nama Pegawai')
                            ->relationship('user', 'name')
                            ->default(fn () => Auth::id())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('jenis_izin_id')
                            ->label('Tipe Izin / Cuti')
                            ->relationship('jenisIzin', 'nama', fn ($query) => $query->where('aktif', true))
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('tanggal_mulai')
                            ->label('Dari Tanggal')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->default(now())
                            ->required(),

                        DatePicker::make('tanggal_selesai')
                            ->label('Sampai Tanggal')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->default(now())
                            ->required(),

                        Textarea::make('alasan')
                            ->label('Alasan / Keterangan')
                            ->placeholder('Jelaskan alasan izin / sakit / cuti / WFH...')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        FileUpload::make('lampiran')
                            ->label('Lampiran Bukti (Surat Dokter / Form Cuti)')
                            ->disk('public')
                            ->directory('lampiran_izin')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(2048)
                            ->helperText('Maksimal ukuran file 2MB (PDF, JPG, PNG).')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Persetujuan / Verifikasi Atasan')
                    ->schema([
                        Select::make('status')
                            ->label('Status Persetujuan')
                            ->options([
                                'menunggu' => 'Menunggu Persetujuan',
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                            ])
                            ->default('menunggu')
                            ->required(),

                        Select::make('disetujui_oleh')
                            ->label('Diverifikasi Oleh')
                            ->relationship('approver', 'name')
                            ->default(fn () => Auth::id())
                            ->searchable()
                            ->preload(),

                        Textarea::make('catatan_atasan')
                            ->label('Catatan Atasan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
