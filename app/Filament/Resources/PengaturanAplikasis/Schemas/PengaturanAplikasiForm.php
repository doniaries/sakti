<?php

namespace App\Filament\Resources\PengaturanAplikasis\Schemas;

use App\Models\User;
use Dotswan\MapPicker\Fields\Map;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
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
                // 1. IDENTITAS SISTEM APLIKASI
                Section::make('Identitas Sistem Aplikasi')
                    ->description('Konfigurasi nama sistem aplikasi, sub-judul kepanjangan, dan ringkasan sistem.')
                    ->icon('heroicon-o-computer-desktop')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nama_aplikasi')
                                    ->label('Nama Aplikasi / Sistem')
                                    ->placeholder('Contoh: SAKTI')
                                    ->required()
                                    ->default('SAKTI')
                                    ->maxLength(100),

                                TextInput::make('sub_nama')
                                    ->label('Sub-Judul (Kepanjangan SAKTI)')
                                    ->placeholder('Contoh: Sistem Administrasi, Kinerja, dan Tata Informasi')
                                    ->required()
                                    ->default('Sistem Administrasi, Kinerja, dan Tata Informasi')
                                    ->helperText('Kepanjangan singkatan SAKTI yang tampil di bawah nama aplikasi.')
                                    ->maxLength(200),
                            ]),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi / Catatan Sistem')
                            ->rows(2)
                            ->placeholder('Sistem Administrasi, Kinerja, dan Tata Informasi')
                            ->columnSpanFull(),
                    ]),

                // 2. LOKASI KANTOR & GEOFENCING (PETA GPS)
                Section::make('Lokasi Kantor & Geofencing Presensi (Peta)')
                    ->description('Tandai titik koordinat kantor melalui peta interaktif di bawah. Latitude dan Longitude otomatis terisi saat pin digeser atau peta diklik.')
                    ->icon('heroicon-o-map-pin')
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
                                    ->helperText('Batas radius absensi pegawai (lingkaran geofence di peta)'),
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

                // 3. INFORMASI VERSI & PENGEMBANG
                Section::make('Informasi Versi & Pengembang')
                    ->description('Rincian versi sistem, pihak pembuat, serta kontak bantuan resmi.')
                    ->icon('heroicon-o-information-circle')
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
                    ]),
            ]);
    }
}
