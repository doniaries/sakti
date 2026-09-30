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
                            ->default(function () {
                                if ($queryTanggal = request()->query('tanggal')) {
                                    return $queryTanggal;
                                }

                                $userId = Auth::id();
                                $latestLengkap = \App\Models\AbsensiHarian::where('user_id', $userId)
                                    ->whereNotNull('log_masuk_id')
                                    ->whereNotNull('log_pulang_id')
                                    ->orderBy('tanggal', 'desc')
                                    ->first();

                                return $latestLengkap ? $latestLengkap->tanggal->format('Y-m-d') : now()->format('Y-m-d');
                            })
                            ->rules([
                                fn(Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $userId = $get('user_id');
                                    if ($userId && $value) {
                                        $absensi = \App\Models\AbsensiHarian::where('user_id', $userId)
                                            ->whereDate('tanggal', $value)
                                            ->first();

                                        if (!$absensi || !$absensi->log_masuk_id || !$absensi->log_pulang_id) {
                                            $fail('LHK hanya dapat dibuat jika absensi Masuk dan Pulang sudah lengkap.');
                                        }
                                    }
                                },
                            ])
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
                            ->default(function () {
                                if ($q = request()->query('log_absensi_masuk_id')) return $q;
                                $latest = \App\Models\AbsensiHarian::where('user_id', Auth::id())
                                    ->whereNotNull('log_masuk_id')->whereNotNull('log_pulang_id')
                                    ->orderBy('tanggal', 'desc')->first();
                                return $latest ? $latest->log_masuk_id : null;
                            }),

                        Forms\Components\Placeholder::make('absensi_masuk_display')
                            ->label('Absensi Masuk')
                            ->content(function ($record, Get $get) {
                                $tanggal = $get('tanggal');
                                $userId = $get('user_id');
                                if ($tanggal && $userId) {
                                    $absensi = \App\Models\AbsensiHarian::where('user_id', $userId)
                                        ->whereDate('tanggal', $tanggal)->first();
                                    if ($absensi && $absensi->log_masuk_id) {
                                        $log = \App\Models\LogAbsensi::find($absensi->log_masuk_id);
                                        if ($log) {
                                            $jam = $log->waktu_absensi?->format('H:i:s') ?? '-';
                                            $sumber = !empty($log->sumber) ? " ({$log->sumber})" : '';
                                            // Warna Amber untuk Masuk
                                            return new \Illuminate\Support\HtmlString("<span style='display:inline-block; padding:0.25rem 0.75rem; border-radius:9999px; background-color:rgba(245, 158, 11, 0.1); color:rgb(180, 83, 9); font-weight:600; font-size:0.875rem;'>🕒 {$jam}{$sumber}</span>");
                                        }
                                    }
                                }
                                return new \Illuminate\Support\HtmlString("<span style='color:gray;'>-</span>");
                            })
                            ->columnSpan(1),

                        // Absensi Pulang — tampil sebagai info
                        Forms\Components\Hidden::make('log_absensi_pulang_id')
                            ->default(function () {
                                if ($q = request()->query('log_absensi_pulang_id')) return $q;
                                $latest = \App\Models\AbsensiHarian::where('user_id', Auth::id())
                                    ->whereNotNull('log_masuk_id')->whereNotNull('log_pulang_id')
                                    ->orderBy('tanggal', 'desc')->first();
                                return $latest ? $latest->log_pulang_id : null;
                            }),

                        Forms\Components\Placeholder::make('absensi_pulang_display')
                            ->label('Absensi Pulang')
                            ->content(function ($record, Get $get) {
                                $tanggal = $get('tanggal');
                                $userId = $get('user_id');
                                if ($tanggal && $userId) {
                                    $absensi = \App\Models\AbsensiHarian::where('user_id', $userId)
                                        ->whereDate('tanggal', $tanggal)->first();
                                    if ($absensi && $absensi->log_pulang_id) {
                                        $log = \App\Models\LogAbsensi::find($absensi->log_pulang_id);
                                        if ($log) {
                                            $jam = $log->waktu_absensi?->format('H:i:s') ?? '-';
                                            $sumber = !empty($log->sumber) ? " ({$log->sumber})" : '';
                                            // Warna Hijau untuk Pulang
                                            return new \Illuminate\Support\HtmlString("<span style='display:inline-block; padding:0.25rem 0.75rem; border-radius:9999px; background-color:rgba(34, 197, 94, 0.1); color:rgb(21, 128, 61); font-weight:600; font-size:0.875rem;'>🕒 {$jam}{$sumber}</span>");
                                        }
                                    }
                                }
                                return new \Illuminate\Support\HtmlString("<span style='color:gray;'>-</span>");
                            })
                            ->columnSpan(1),

                        Select::make('status_kehadiran')
                            ->options([
                                'Hadir'     => 'Hadir',
                                'Sakit'     => 'Sakit',
                                'Izin'      => 'Izin',
                                'Dinas Luar' => 'Dinas Luar',
                            ])
                            ->default(request()->query('status_kehadiran', 'Hadir'))
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
                                        1 => 'Diluar Sasaran Kinerja',
                                        2 => 'Melaksanakan Tugas Lain yang diberikan Pimpinan'
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
                $set('status_kehadiran', $absensi->status ?? 'Hadir');
            } else {
                $set('log_absensi_masuk_id', null);
                $set('log_absensi_pulang_id', null);
                $set('status_kehadiran', 'Tanpa Keterangan'); // atau default lain
            }
        }
    }
}
