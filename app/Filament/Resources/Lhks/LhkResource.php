<?php

namespace App\Filament\Resources\Lhks;

use App\Filament\Resources\Lhks\Pages;
use App\Filament\Resources\Lhks\Pages\CreateLhk;
use App\Filament\Resources\Lhks\Pages\EditLhk;
use App\Filament\Resources\Lhks\Pages\ListLhks;
use App\Filament\Resources\Lhks\Schemas\LhkForm;
use App\Models\Lhk;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LhkResource extends Resource
{

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'LHK';
    protected static ?string $model = Lhk::class;
    protected static ?string $modelLabel = 'Laporan LHK';
    protected static ?string $pluralModelLabel = 'Laporan LHK';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && $user->hasRole('Pegawai')) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
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
                            ->relationship('logAbsensiMasuk', 'waktu_absensi')
                            ->default(request()->query('log_absensi_masuk_id'))
                            ->label('Absensi Masuk')
                            ->columnSpan(1),
                        Select::make('log_absensi_pulang_id')
                            ->relationship('logAbsensiPulang', 'waktu_absensi')
                            ->default(request()->query('log_absensi_pulang_id'))
                            ->label('Absensi Pulang')
                            ->columnSpan(1),
                        Select::make('status')
                            ->options([
                                'Hadir' => 'Hadir',
                                'Sakit' => 'Sakit',
                                'Izin' => 'Izin',
                                'Dinas Luar' => 'Dinas Luar'
                            ])
                            ->default(request()->query('status', 'Hadir'))
                            ->columnSpanFull(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make('Rincian Pekerjaan')
                    ->schema([
                        Forms\Components\Repeater::make('pekerjaans')
                            ->relationship('pekerjaans')
                            ->schema([
                                Forms\Components\TimePicker::make('jam_mulai')
                                    ->required()
                                    ->columnSpan(1),
                                Forms\Components\TimePicker::make('jam_selesai')
                                    ->required()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('lama_menit')
                                    ->numeric()
                                    ->default(0)
                                    ->columnSpan(1),
                                Forms\Components\Select::make('rencana_aksi_id')
                                    ->relationship('rencanaAksi', 'nama')
                                    ->searchable()
                                    ->columnSpan(1),
                                Forms\Components\Select::make('aktivitas_id')
                                    ->relationship('aktivitas', 'nama')
                                    ->searchable()
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('hasil_pekerjaan')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('bukti_url')
                                    ->url()
                                    ->label('Link Bukti')
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->collapsible()
                            ->defaultItems(1)
                    ])
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('logAbsensiMasuk.waktu_absensi')
                    ->label('Masuk')
                    ->time(),
                Tables\Columns\TextColumn::make('logAbsensiPulang.waktu_absensi')
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
            ])
            ->emptyStateActions([
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
            'index' => Pages\ListLhks::route('/'),
            'create' => Pages\CreateLhk::route('/create'),
            'edit' => Pages\EditLhk::route('/{record}/edit'),
        ];
    }
}
