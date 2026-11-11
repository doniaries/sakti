<?php

namespace App\Filament\Resources\PengaturanAplikasis\Schemas;

use Dotswan\MapPicker\Fields\Map;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PengaturanAplikasiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Pengaturan Sistem')
                    ->contained()
                    ->columnSpanFull()
                    ->tabs([
                        // =========================================================================
                        // TAB 1: IDENTITAS SISTEM & TAHUN ANGGARAN AKTIF
                        // =========================================================================
                        Tab::make('Identitas & Anggaran Sistem')
                            ->icon('heroicon-o-computer-desktop')
                            ->schema([
                                Section::make('Identitas Aplikasi SAKTI')
                                    ->description('Konfigurasi nama sistem aplikasi, sub-judul kepanjangan, dan ringkasan sistem.')
                                    ->icon('heroicon-o-sparkles')
                                    ->compact()
                                    ->schema([
                                        Grid::make(['default' => 1, 'md' => 3])
                                            ->schema([
                                                TextInput::make('nama_aplikasi')
                                                    ->label('Nama Aplikasi / Sistem')
                                                    ->placeholder('Contoh: SAKTI')
                                                    ->required()
                                                    ->default('SAKTI')
                                                    ->maxLength(100),

                                                TextInput::make('sub_nama')
                                                    ->label('Sub-Judul (Kepanjangan SAKTI)')
                                                    ->placeholder('Contoh: Sistem Administrasi Kinerja Terintegrasi')
                                                    ->required()
                                                    ->default('Sistem Administrasi Kinerja Terintegrasi')
                                                    ->helperText('Kepanjangan singkatan SAKTI yang tampil di header dan portal.')
                                                    ->columnSpan(['md' => 2])
                                                    ->maxLength(200),
                                            ]),

                                        Textarea::make('deskripsi')
                                            ->label('Deskripsi / Ringkasan Sistem')
                                            ->rows(2)
                                            ->placeholder('Sistem Administrasi Kinerja Terintegrasi')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Konfigurasi Anggaran & Dukungan Teknis')
                                    ->description('Tahun anggaran global yang berlaku untuk seluruh modul keuangan serta kontak pengembang.')
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->compact()
                                    ->schema([
                                        Grid::make(['default' => 1, 'md' => 4])
                                            ->schema([
                                                TextInput::make('tahun_anggaran_aktif')
                                                    ->label('Tahun Anggaran Aktif')
                                                    ->helperText('Modul DPA, RKA, & SPJ otomatis mengacu ke tahun ini.')
                                                    ->numeric()
                                                    ->prefixIcon('heroicon-m-calendar-days')
                                                    ->minValue(2020)
                                                    ->maxValue(2099)
                                                    ->required()
                                                    ->default((int) date('Y')),

                                                TextInput::make('versi_aplikasi')
                                                    ->label('Versi Aplikasi')
                                                    ->placeholder('Contoh: v1.0.0')
                                                    ->prefixIcon('heroicon-m-tag')
                                                    ->required()
                                                    ->default('v1.0.0'),

                                                TextInput::make('pembuat_aplikasi')
                                                    ->label('Pengembang Aplikasi')
                                                    ->placeholder('Contoh: Diskominfotik')
                                                    ->prefixIcon('heroicon-m-user-group')
                                                    ->default('Diskominfotik'),

                                                TextInput::make('kontak_bantuan')
                                                    ->label('Kontak / Email Bantuan')
                                                    ->placeholder('Contoh: helpdesk@lhk.go.id')
                                                    ->prefixIcon('heroicon-m-envelope'),
                                            ]),
                                    ]),
                            ]),

                        // =========================================================================
                        // TAB 2: LOKASI KANTOR & GEOFENCING GPS PRESENSI
                        // =========================================================================
                        Tab::make('Lokasi Kantor & Presensi GPS')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Section::make('Titik Koordinat & Radius Kantor')
                                    ->description('Tandai titik koordinat kantor dan batas radius presensi pegawai. Latitude dan Longitude otomatis terisi saat pin digeser atau tombol deteksi diklik.')
                                    ->icon('heroicon-o-map')
                                    ->compact()
                                    ->headerActions([
                                        Action::make('ambil_lokasi_laptop')
                                            ->label('Ambil Titik Lokasi Laptop (GPS)')
                                            ->icon('heroicon-m-map-pin')
                                            ->color('warning')
                                            ->tooltip('Deteksi titik koordinat perangkat laptop saat ini secara otomatis menggunakan sensor browser GPS')
                                            ->alpineClickHandler(<<<'JS'
                                                if (!navigator.geolocation) {
                                                    new FilamentNotification()
                                                        .title('Geolocation Tidak Didukung')
                                                        .body('Browser Anda tidak mendukung deteksi lokasi Geolocation.')
                                                        .danger()
                                                        .send();
                                                    return;
                                                }

                                                new FilamentNotification()
                                                    .title('Mendeteksi Lokasi Laptop...')
                                                    .body('Sedang membaca koordinat GPS perangkat Anda.')
                                                    .info()
                                                    .send();

                                                navigator.geolocation.getCurrentPosition(
                                                    (position) => {
                                                        const lat = parseFloat(position.coords.latitude.toFixed(7));
                                                        const lng = parseFloat(position.coords.longitude.toFixed(7));
                                                        
                                                        $wire.set('data.latitude_kantor', lat);
                                                        $wire.set('data.longitude_kantor', lng);
                                                        $wire.set('data.lokasi', { lat: lat, lng: lng });

                                                        new FilamentNotification()
                                                            .title('Koordinat Berhasil Diperbarui!')
                                                            .body('Titik lokasi kantor berhasil diatur ke posisi laptop Anda: ' + lat + ', ' + lng)
                                                            .success()
                                                            .send();
                                                    },
                                                    (error) => {
                                                        new FilamentNotification()
                                                            .title('Gagal Mendeteksi Lokasi')
                                                            .body('Pastikan izin akses lokasi diizinkan pada browser Anda (' + error.message + ').')
                                                            .danger()
                                                            .send();
                                                    },
                                                    { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
                                                );
                                            JS),
                                    ])
                                    ->schema([
                                        Grid::make(['default' => 1, 'md' => 3])
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
                                                    ->helperText('Batas toleransi lingkaran geofence presensi'),
                                            ]),

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
                                                'min-height: 440px',
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
                                    ]),
                            ]),
                    ]),
            ]);
    }
}

