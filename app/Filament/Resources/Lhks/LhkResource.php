<?php

namespace App\Filament\Resources\Lhks;

use App\Filament\Resources\Lhks\Pages\CreateLhk;
use App\Filament\Resources\Lhks\Pages\EditLhk;
use App\Filament\Resources\Lhks\Pages\ListLhks;
use App\Filament\Resources\Lhks\Schemas\LhkForm;
use App\Filament\Resources\Lhks\Tables\LhksTable;
use App\Models\Lhk;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LhkResource extends Resource
{
    protected static ?string $model = Lhk::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kehadiran')
                    ->schema([
                        DatePicker::make('tanggal')
                            ->required()
                            ->default(now()),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable(),
                        Select::make('log_absensi_masuk_id')
                            ->relationship('logAbsensiMasuk', 'waktu')
                            ->label('Absensi Masuk'),
                        Select::make('log_absensi_pulang_id')
                            ->relationship('logAbsensiPulang', 'waktu')
                            ->label('Absensi Pulang'),
                        Select::make('status')
                            ->options([
                                'Hadir' => 'Hadir',
                                'Sakit' => 'Sakit',
                                'Izin' => 'Izin',
                                'Dinas Luar' => 'Dinas Luar'
                            ])->default('Hadir'),
                    ])->columns(2),

                Forms\Components\Section::make('Rincian Pekerjaan')
                    ->schema([
                        Forms\Components\Repeater::make('pekerjaans')
                            ->relationship('pekerjaans')
                            ->schema([
                                Forms\Components\TimePicker::make('jam_mulai')
                                    ->required(),
                                Forms\Components\TimePicker::make('jam_selesai')
                                    ->required(),
                                Forms\Components\TextInput::make('lama_menit')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\Select::make('rencana_aksi_id')
                                    ->relationship('rencanaAksi', 'nama')
                                    ->searchable(),
                                Forms\Components\Select::make('aktivitas_id')
                                    ->relationship('aktivitas', 'nama')
                                    ->searchable(),
                                Forms\Components\Textarea::make('hasil_pekerjaan')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('bukti_url')
                                    ->url()
                                    ->label('Link Bukti')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->defaultItems(1)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['user', 'pekerjaans', 'logAbsensiMasuk', 'logAbsensiPulang']))
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('logAbsensiMasuk.waktu')
                    ->label('Masuk')
                    ->time(),
                Tables\Columns\TextColumn::make('logAbsensiPulang.waktu')
                    ->label('Pulang')
                    ->time(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'Hadir',
                        'warning' => 'Sakit',
                        'danger' => fn($state) => in_array($state, ['Izin', 'Dinas Luar']),
                    ]),
                Tables\Columns\IconColumn::make('is_approved')
                    ->boolean()
                    ->label('Approved?'),
            ])
            ->filters([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLhks::route('/'),
            'create' => CreateLhk::route('/create'),
            'edit' => EditLhk::route('/{record}/edit'),
        ];
    }
}
