<?php

namespace App\Filament\Resources\Lhks\Schemas;

use App\Models\Aktivitas;
use App\Models\LogAbsensi;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class LhkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Kehadiran')
                    ->schema([
                        DatePicker::make('tanggal')
                            ->required()
                            ->default(request()->query('tanggal', now()))
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::updateKehadiran($set, $get);
                            })
                            ->columnSpan(1),

                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->default(request()->query('user_id', Auth::id()))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::updateKehadiran($set, $get);
                            })
                            ->columnSpan(1),

                        // Absensi Masuk — tampil sebagai info
                        Forms\Components\Hidden::make('log_absensi_masuk_id')
                            ->default(request()->query('log_absensi_masuk_id')),

                        Forms\Components\Placeholder::make('absensi_masuk_display')
                            ->label('Absensi Masuk')
                            ->content(function ($record, $get) {
                                $id = $get('log_absensi_masuk_id');
                                if ($id) {
                                    $log = LogAbsensi::find((int) $id);
                                    return $log ? ($log->waktu_absensi?->format('H:i') . ' (' . $log->sumber . ')') : '-';
                                }
                                return $record?->logAbsensiMasuk
                                    ? ($record->logAbsensiMasuk->waktu_absensi?->format('H:i') . ' (' . $record->logAbsensiMasuk->sumber . ')')
                                    : '-';
                            })
                            ->columnSpan(1),

                        // Absensi Pulang — tampil sebagai info
                        Forms\Components\Hidden::make('log_absensi_pulang_id')
                            ->default(request()->query('log_absensi_pulang_id')),

                        Forms\Components\Placeholder::make('absensi_pulang_display')
                            ->label('Absensi Pulang')
                            ->content(function ($record, $get) {
                                $id = $get('log_absensi_pulang_id');
                                if ($id) {
                                    $log = LogAbsensi::find((int) $id);
                                    return $log ? ($log->waktu_absensi?->format('H:i') . ' (' . $log->sumber . ')') : '-';
                                }
                                return $record?->logAbsensiPulang
                                    ? ($record->logAbsensiPulang->waktu_absensi?->format('H:i') . ' (' . $record->logAbsensiPulang->sumber . ')')
                                    : '-';
                            })
                            ->columnSpan(1),

                        Select::make('status')
                            ->options([
                                'Hadir'     => 'Hadir',
                                'Sakit'     => 'Sakit',
                                'Izin'      => 'Izin',
                                'Dinas Luar' => 'Dinas Luar',
                            ])
                            ->default(request()->query('status', 'Hadir'))
                            ->disabled() // Tidak bisa diedit sesuai permintaan
                            ->dehydrated() // Tetap simpan ke database
                            ->columnSpan(1),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make('Rincian Pekerjaan')
                    ->schema([
                        Forms\Components\Repeater::make('pekerjaans')
                            ->relationship('pekerjaans')
                            ->schema([
                                // Baris 1: Rencana Aksi & Aktivitas
                                Select::make('rencana_aksi_id')
                                    ->label('Rencana Aksi')
                                    ->options([
                                        1 => 'Sasaran',
                                        2 => 'Diluar Sasaran Kinerja',
                                        3 => 'Melaksanakan Tugas Lain yang diberikan Pimpinan'
                                    ])
                                    ->searchable()
                                    ->required()
                                    ->preload()
                                    ->columnSpan(1),

                                Select::make('aktivitas_id')
                                    ->relationship('aktivitas', 'nama_aktivitas')
                                    ->getOptionLabelFromRecordUsing(
                                        fn($record) => $record->nama_aktivitas . ' (' . ($record->menit ?? 0) . ' menit)'
                                    )
                                    ->searchable()
                                    ->required()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if ($state) {
                                            $aktivitas = Aktivitas::find((int) $state);
                                            $menit = $aktivitas?->menit ?? 0;
                                            $set('lama_menit', $menit);
                                            $jamMulai = $get('jam_mulai');
                                            if ($jamMulai && $menit > 0) {
                                                try {
                                                    $set('jam_selesai', Carbon::parse($jamMulai)
                                                        ->addMinutes((int) $menit)
                                                        ->format('H:i'));
                                                } catch (\Exception $e) {
                                                }
                                            }
                                        } else {
                                            $set('lama_menit', 0);
                                        }
                                    })
                                    ->label('Aktivitas')
                                    ->columnSpan(1),

                                // Baris 2: Waktu (Grid 3 kolom)
                                \Filament\Schemas\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TimePicker::make('jam_mulai')
                                            ->required()
                                            ->seconds(false)
                                            ->live()
                                            ->afterStateUpdated(function ($state, $set, $get) {
                                                $lamaMenit = (int) ($get('lama_menit') ?? 0);
                                                if ($state && $lamaMenit > 0) {
                                                    try {
                                                        $set('jam_selesai', Carbon::parse($state)
                                                            ->addMinutes($lamaMenit)
                                                            ->format('H:i'));
                                                    } catch (\Exception $e) {
                                                    }
                                                }
                                            })
                                            ->label('Jam Mulai'),

                                        Forms\Components\TimePicker::make('jam_selesai')
                                            ->required()
                                            ->seconds(false)
                                            ->label('Jam Selesai'),

                                        Forms\Components\TextInput::make('lama_menit')
                                            ->numeric()
                                            ->default(0)
                                            ->suffix('menit')
                                            ->readOnly()
                                            ->label('Lama (menit)'),
                                    ])
                                    ->columnSpanFull(),

                                // Baris 3: Hasil & Bukti
                                Forms\Components\Textarea::make('hasil_pekerjaan')
                                    ->label('Hasil Pekerjaan')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('bukti_url')
                                    ->url()
                                    ->label('Link Bukti (Google Drive)')
                                    ->helperText('Pastikan link dapat diakses (Anyone with the link)')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->defaultItems(1)
                            ->addActionLabel('+ Tambah Pekerjaan'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function updateKehadiran(Set $set, Get $get): void
    {
        $userId = $get('user_id');
        $tanggal = $get('tanggal');

        if ($userId && $tanggal) {
            $absensi = \App\Models\AbsensiHarian::where('user_id', $userId)
                ->whereDate('tanggal', $tanggal)
                ->first();

            if ($absensi) {
                $set('log_absensi_masuk_id', $absensi->log_masuk_id);
                $set('log_absensi_pulang_id', $absensi->log_pulang_id);
                $set('status', $absensi->status ?? 'Hadir');
            } else {
                $set('log_absensi_masuk_id', null);
                $set('log_absensi_pulang_id', null);
                $set('status', 'Tanpa Keterangan'); // atau default lain
            }
        }
    }
}
