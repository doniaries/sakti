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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PengajuanIzinForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pengajuan Izin, Sakit & Cuti')
                    ->description('Isi data pengajuan ketidakhadiran kerja (izin / sakit / cuti).')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('user_id')
                            ->label('Nama Pegawai')
                            ->relationship('user', 'name')
                            ->default(fn () => Auth::id())
                            ->disabled(function (): bool {
                                /** @var User|null $user */
                                $user = Auth::user();

                                return ! ($user?->hasAnyRole(['super_admin', 'admin']) ?? false);
                            })
                            ->dehydrated()
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('jenis_izin_id')
                            ->label('Tipe Izin / Cuti')
                            ->relationship('jenisIzin', 'nama', fn ($query) => $query->where('aktif', true))
                            ->searchable()
                            ->preload()
                            ->live()
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
                            ->placeholder('Jelaskan alasan izin / sakit / cuti...')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        FileUpload::make('lampiran')
                            ->label('Surat Keterangan Dokter (Wajib untuk Sakit)')
                            ->disk('public')
                            ->directory('lampiran_izin')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->rules(['file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'])
                            ->maxSize(1024)
                            ->visible(fn (Get $get): bool => JenisIzin::whereKey($get('jenis_izin_id'))->value('kategori') === 'sakit')
                            ->required(fn (Get $get): bool => JenisIzin::whereKey($get('jenis_izin_id'))->value('kategori') === 'sakit')
                            ->helperText('Khusus izin sakit. Maksimal ukuran file 1MB (PDF, JPG, PNG).')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Persetujuan / Verifikasi Atasan')
                    ->hiddenOn('create')
                    ->schema([
                        Select::make('status')
                            ->label('Status Persetujuan')
                            ->options([
                                'menunggu' => 'Menunggu Persetujuan',
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                            ])
                            ->default('menunggu')
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Select::make('disetujui_oleh')
                            ->label('Diverifikasi Oleh')
                            ->relationship('approver', 'name')
                            ->placeholder('Belum diverifikasi')
                            ->disabled()
                            ->dehydrated(),

                        Textarea::make('catatan_atasan')
                            ->label('Catatan Atasan')
                            ->rows(2)
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
