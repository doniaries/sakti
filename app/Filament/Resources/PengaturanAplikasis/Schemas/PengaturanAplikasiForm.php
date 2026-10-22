<?php

namespace App\Filament\Resources\PengaturanAplikasis\Schemas;

use Dotswan\MapPicker\Fields\Map;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PengaturanAplikasiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Aplikasi & Logo')
                    ->description('Konfigurasi nama sistem, logo instansi, dan sub-judul yang tampil pada dashboard mobile maupun cetak laporan.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nama_aplikasi')
                                    ->label('Nama Aplikasi')
                                    ->placeholder('Contoh: ABSENSI LHK')
                                    ->required()
                                    ->default('ABSENSI LHK')
                                    ->maxLength(100),

                                TextInput::make('sub_nama')
                                    ->label('Sub-Judul / Instansi')
                                    ->placeholder('Contoh: Dinas Lingkungan Hidup')
                                    ->default('Dinas Lingkungan Hidup')
                                    ->maxLength(150),
                            ]),

                        FileUpload::make('logo')
                            ->label('Logo Aplikasi / Instansi')
                            ->helperText('Format file gambar (PNG, JPG, SVG). Disarankan rasio kotak / persegi.')
                            ->image()
                            ->directory('aplikasi')
                            ->imageEditor()
                            ->maxSize(2048),
                    ]),

                Section::make('Lokasi Kantor & Geofencing Presensi')
                    ->description('Tandai titik koordinat kantor melalui peta interaktif di bawah. Latitude dan Longitude otomatis terisi saat pin digeser atau peta diklik.')
                    ->schema([
                        Map::make('lokasi')
                            ->label('Peta Titik Koordinat & Radius Kantor')
                            ->columnSpanFull()
                            ->defaultLocation(latitude: -0.6975230, longitude: 100.9854720)
                            ->afterStateHydrated(function ($state, $record, Set $set): void {
                                if ($record && $record->latitude_kantor && $record->longitude_kantor) {
                                    $set('lokasi', [
                                        'lat' => (float) $record->latitude_kantor,
                                        'lng' => (float) $record->longitude_kantor,
                                    ]);
                                }
                            })
                            ->afterStateUpdated(function (Set $set, ?array $state): void {
                                if (is_array($state) && isset($state['lat'], $state['lng'])) {
                                    $set('latitude_kantor', round((float) $state['lat'], 7));
                                    $set('longitude_kantor', round((float) $state['lng'], 7));
                                }
                            })
                            ->extraStyles([
                                'min-height: 400px',
                                'border-radius: 1rem',
                                'overflow: hidden',
                                'border: 1px solid rgba(148, 163, 184, 0.3)',
                            ])
                            ->rangeSelectField('radius_meter')
                            ->showMarker()
                            ->markerColor('#ef4444')
                            ->showMyLocationButton()
                            ->showFullscreenControl()
                            ->showZoomControl()
                            ->clickable(true)
                            ->draggable()
                            ->zoom(16)
                            ->detectRetina()
                            ->dehydrated(false),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('latitude_kantor')
                                    ->label('Latitude Kantor')
                                    ->placeholder('-0.6975230')
                                    ->numeric()
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        $lng = $get('longitude_kantor');
                                        if ($state && $lng) {
                                            $set('lokasi', ['lat' => (float) $state, 'lng' => (float) $lng]);
                                        }
                                    })
                                    ->helperText('Garis lintang (terisi otomatis dari peta)'),

                                TextInput::make('longitude_kantor')
                                    ->label('Longitude Kantor')
                                    ->placeholder('100.9854720')
                                    ->numeric()
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        $lat = $get('latitude_kantor');
                                        if ($state && $lat) {
                                            $set('lokasi', ['lat' => (float) $lat, 'lng' => (float) $state]);
                                        }
                                    })
                                    ->helperText('Garis bujur (terisi otomatis dari peta)'),

                                TextInput::make('radius_meter')
                                    ->label('Radius Presensi (Meter)')
                                    ->placeholder('150')
                                    ->numeric()
                                    ->required()
                                    ->default(150)
                                    ->suffix('Meter')
                                    ->live(onBlur: true)
                                    ->helperText('Batas jarak absensi pegawai (lingkaran geofence di peta)'),
                            ]),

                        TextInput::make('alamat_kantor')
                            ->label('Alamat / Nama Gedung Kantor')
                            ->placeholder('Contoh: Jl. Prof. M. Yamin, SH No. 1, Muaro Sijunjung')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Section::make('Informasi Versi & Pengembang')
                    ->description('Rincian versi sistem, pihak pembuat, serta kontak bantuan resmi.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('versi_aplikasi')
                                    ->label('Versi Aplikasi')
                                    ->placeholder('Contoh: v1.0.0')
                                    ->required()
                                    ->default('v1.0.0'),

                                TextInput::make('pembuat_aplikasi')
                                    ->label('Pembuat / Developer Aplikasi')
                                    ->placeholder('Contoh: Diskominfotik')
                                    ->default('Diskominfotik'),

                                TextInput::make('kontak_bantuan')
                                    ->label('Kontak / Email Bantuan')
                                    ->placeholder('Contoh: helpdesk@lhk.go.id'),
                            ]),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi / Catatan Sistem')
                            ->rows(3)
                            ->placeholder('Sistem Presensi & Laporan Harian Kinerja Pegawai')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
