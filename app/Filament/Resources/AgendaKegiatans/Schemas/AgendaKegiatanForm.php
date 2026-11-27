<?php

namespace App\Filament\Resources\AgendaKegiatans\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AgendaKegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Jadwal Kegiatan')
                    ->schema([
                        TextInput::make('judul')
                            ->label('Judul / Perihal Kegiatan')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Grid::make(['default' => 1, 'md' => 3])
                            ->schema([
                                DatePicker::make('tanggal')
                                    ->label('Tanggal Pelaksanaan')
                                    ->displayFormat('d/m/Y')
                                    ->required(),
                                TimePicker::make('jam_mulai')
                                    ->label('Jam Mulai')
                                    ->seconds(false)
                                    ->required(),
                                TimePicker::make('jam_selesai')
                                    ->label('Jam Selesai (Opsional)')
                                    ->seconds(false)
                                    ->helperText('Kosongkan jika tertulis "Selesai"'),
                            ]),
                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                TextInput::make('tempat')
                                    ->label('Tempat / Lokasi')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('asal_undangan')
                                    ->label('Asal Undangan')
                                    ->placeholder('Contoh: Kominfo / Bupati Sijunjung')
                                    ->maxLength(255),
                            ]),
                        Textarea::make('keterangan')
                            ->label('Keterangan Singkat')
                            ->rows(3)
                            ->columnSpanFull(),
                        FileUpload::make('lampiran_file')
                            ->label('Lampiran Surat / Undangan')
                            ->directory('agenda-kegiatan')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->columnSpanFull(),
                        Hidden::make('created_by')
                            ->default(fn () => Auth::id()),
                    ]),
                Section::make('Disposisi (Orang yang Diperintahkan)')
                    ->description('Pilih satu atau banyak pegawai/pejabat yang ditugaskan dengan satu instruksi bersama.')
                    ->schema([
                        Select::make('disposisi_user_ids')
                            ->label('Pegawai / Pejabat yang Ditugaskan')
                            ->options(fn (): array => User::query()
                                ->where('aktif', true)
                                ->orderBy('id')
                                ->pluck('name', 'id')
                                ->all())
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('disposisi_instruksi')
                            ->label('Instruksi / Arahan')
                            ->placeholder('Contoh: Hadir, bawa 1 org admin')
                            ->helperText('Berlaku untuk seluruh pegawai/pejabat yang dipilih di atas.')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
