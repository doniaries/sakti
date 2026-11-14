<?php

namespace App\Filament\Resources\Instansis\Schemas;

use App\Models\Bidang;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InstansiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identitas Instansi')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        TextInput::make('nama_dinas')
                            ->label('Nama Instansi / Dinas')
                            ->placeholder('Contoh: DINAS PARIWISATA, PEMUDA DAN OLAH RAGA')
                            ->required()
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('kode_instansi')
                                    ->label('Kode Unit Organisasi (SKPD)')
                                    ->placeholder('3.26.2.19.0.00.01.0000')
                                    ->mask('9.99.9.99.9.99.99.9999')
                                    ->default('3.26.2.19.0.00.01.0000')
                                    ->helperText('Format SIPD: 3.26.2.19.0.00.01.0000'),

                                TextInput::make('sub_unit_organisasi')
                                    ->label('Sub Unit Organisasi')
                                    ->placeholder('-')
                                    ->default('-'),
                            ]),

                        Textarea::make('alamat')
                            ->label('Alamat Kantor')
                            ->rows(3)
                            ->columnSpanFull(),

                        FileUpload::make('logo')
                            ->label('Logo Resmi Instansi')
                            ->image()
                            ->disk('public')
                            ->directory('instansis')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                            ->helperText('Format yang diizinkan: PNG, JPG, JPEG. Maksimal 2MB.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Kepala Dinas')
                    ->icon('heroicon-o-user-group')
                    ->description('Tentukan Kepala Dinas')
                    ->columns(2)
                    ->schema([
                        Select::make('kepala_id')
                            ->label('Akun Kepala Dinas (Kadis)')
                            ->relationship('kepala', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $user = User::find($state);
                                    if ($user) {
                                        $set('nama_kepala_dinas', $user->name);
                                    }
                                }
                            })
                            ->placeholder('Pilih Akun Kepala Dinas (Pegawai)'),

                        TextInput::make('nama_kepala_dinas')
                            ->label('Nama Lengkap & Gelar Kadis')
                            ->placeholder('Nama Lengkap (Otomatis dari akun atau ketik manual)'),
                    ]),

                Section::make('Sekretaris Daerah (Atasan Kepala Dinas)')
                    ->icon('heroicon-o-building-library')
                    ->description('Pejabat penandatangan cuti apabila Kepala Dinas mengajukan cuti')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama_sekda')
                            ->label('Nama Lengkap & Gelar Sekda')
                            ->placeholder('Contoh: Dr. H. Zefnihan, AP., M.Si.'),

                        TextInput::make('nip_sekda')
                            ->label('NIP Sekretaris Daerah')
                            ->placeholder('Contoh: 19740512 199311 1 001'),
                    ]),

                Section::make('Sekretaris & Kepala Bidang')
                    ->icon('heroicon-o-briefcase')
                    ->description('Tentukan Sekretaris Dinas serta Kepala Bidang pada masing-masing bidang')
                    ->columns(2)
                    ->schema(function () {
                        $userOptions = User::query()->orderBy('name')->pluck('name', 'id')->all();

                        $fields = [
                            Select::make('sekretaris_id')
                                ->label('Sekretaris Dinas')
                                ->options($userOptions)
                                ->searchable()
                                ->preload()
                                ->placeholder('Pilih Pegawai sebagai Sekretaris Dinas')
                                ->dehydrated(true)
                                ->columnSpan(1),
                        ];

                        $bidangs = Bidang::where('slug', 'not like', '%sekretariat%')->get();

                        foreach ($bidangs as $bidang) {
                            $fields[] = Select::make('kabid_' . $bidang->id)
                                ->label('Kabid ' . $bidang->nama)
                                ->options($userOptions)
                                ->searchable()
                                ->preload()
                                ->placeholder('Pilih Kepala Bidang')
                                ->dehydrated(true)
                                ->columnSpan(1);
                        }

                        return $fields;
                    }),
            ]);
    }
}
