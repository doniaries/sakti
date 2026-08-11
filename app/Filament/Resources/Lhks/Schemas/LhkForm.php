<?php

namespace App\Filament\Resources\Lhks\Schemas;

use App\Models\Aktivitas;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Components\Section;
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
                            ->columnSpan(1),

                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->default(request()->query('user_id', Auth::id()))
                            ->searchable()
                            ->columnSpan(1),

                        Select::make('log_absensi_masuk_id')
                            ->relationship(
                                'logAbsensiMasuk',
                                'waktu_absensi',
                                fn ($query) => $query->orderBy('waktu_absensi')
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn ($record) => $record->waktu_absensi?->format('H:i') . ' (' . $record->sumber . ')'
                            )
                            ->default(request()->query('log_absensi_masuk_id'))
                            ->label('Absensi Masuk')
                            ->columnSpan(1),

                        Select::make('log_absensi_pulang_id')
                            ->relationship(
                                'logAbsensiPulang',
                                'waktu_absensi',
                                fn ($query) => $query->orderBy('waktu_absensi')
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn ($record) => $record->waktu_absensi?->format('H:i') . ' (' . $record->sumber . ')'
                            )
                            ->default(request()->query('log_absensi_pulang_id'))
                            ->label('Absensi Pulang')
                            ->columnSpan(1),

                        Select::make('status')
                            ->options([
                                'Hadir'     => 'Hadir',
                                'Sakit'     => 'Sakit',
                                'Izin'      => 'Izin',
                                'Dinas Luar' => 'Dinas Luar',
                            ])
                            ->default(request()->query('status', 'Hadir'))
                            ->columnSpan(1),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make('Rincian Pekerjaan')
                    ->schema([
                        Forms\Components\Repeater::make('pekerjaans')
                            ->relationship('pekerjaans')
                            ->schema([
                                // 1. Aktivitas — menentukan lama menit & jam selesai
                                Select::make('aktivitas_id')
                                    ->relationship('aktivitas', 'nama_aktivitas')
                                    ->getOptionLabelFromRecordUsing(
                                        fn ($record) => $record->nama_aktivitas . ' (' . ($record->menit ?? 0) . ' menit)'
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if ($state) {
                                            $aktivitas = Aktivitas::find((int) $state);
                                            $menit = $aktivitas?->menit ?? 0;
                                            $set('lama_menit', $menit);
                                            $jamMulai = $get('jam_mulai');
                                            if ($jamMulai && $menit > 0) {
                                                $set('jam_selesai', Carbon::createFromFormat('H:i', substr($jamMulai, 0, 5))
                                                    ->addMinutes((int) $menit)
                                                    ->format('H:i'));
                                            }
                                        } else {
                                            $set('lama_menit', 0);
                                        }
                                    })
                                    ->label('Aktivitas')
                                    ->columnSpan(1),

                                // 2. Rencana Aksi
                                Select::make('rencana_aksi_id')
                                    ->relationship('rencanaAksi', 'rencana_aksi')
                                    ->getOptionLabelFromRecordUsing(
                                        fn ($record) => $record->rencana_aksi
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->label('Rencana Aksi')
                                    ->columnSpan(1),

                                // 3. Lama menit (auto dari aktivitas, read-only)
                                Forms\Components\TextInput::make('lama_menit')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('menit')
                                    ->readOnly()
                                    ->label('Lama (menit)')
                                    ->columnSpan(1),

                                // 4. Jam mulai H:i — update jam selesai otomatis
                                Forms\Components\TimePicker::make('jam_mulai')
                                    ->required()
                                    ->seconds(false)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $lamaMenit = (int) ($get('lama_menit') ?? 0);
                                        if ($state && $lamaMenit > 0) {
                                            $set('jam_selesai', Carbon::createFromFormat('H:i', substr($state, 0, 5))
                                                ->addMinutes($lamaMenit)
                                                ->format('H:i'));
                                        }
                                    })
                                    ->label('Jam Mulai')
                                    ->columnSpan(1),

                                // 5. Jam selesai (auto)
                                Forms\Components\TimePicker::make('jam_selesai')
                                    ->required()
                                    ->seconds(false)
                                    ->label('Jam Selesai')
                                    ->columnSpan(1),

                                // 6. Hasil & Bukti
                                Forms\Components\Textarea::make('hasil_pekerjaan')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('bukti_url')
                                    ->url()
                                    ->label('Link Bukti')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->collapsible()
                            ->defaultItems(1)
                            ->addActionLabel('+ Tambah Pekerjaan'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
