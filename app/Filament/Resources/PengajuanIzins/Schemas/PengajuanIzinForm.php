<?php

namespace App\Filament\Resources\PengajuanIzins\Schemas;

use App\Enums\JenisKelamin;
use App\Models\HariLibur;
use App\Models\JenisIzin;
use App\Models\PengajuanIzin;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PengajuanIzinForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pengajuan Izin, Sakit & Cuti')
                    ->description('Isi data pengajuan ketidakhadiran kerja (izin / sakit / cuti).')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('user_id')
                            ->label('Nama Pegawai')
                            ->relationship('user', 'name')
                            ->default(fn () => Auth::id())
                            ->visible(function (): bool {
                                /** @var User|null $user */
                                $user = Auth::user();

                                return $user?->hasAnyRole(['super_admin', 'admin']) ?? false;
                            })
                            ->dehydrated()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),

                        Select::make('jenis_izin_id')
                            ->label('Tipe Izin / Cuti')
                            ->options(function (Get $get): array {
                                $targetUserId = (int) ($get('user_id') ?: Auth::id());
                                $targetUser = $targetUserId > 0 ? User::find($targetUserId) : null;
                                $isPerempuan = $targetUser?->jenis_kelamin === JenisKelamin::PEREMPUAN;

                                return JenisIzin::query()
                                    ->where('aktif', true)
                                    ->when(! $isPerempuan, fn ($q) => $q->where('kode', '!=', 'cuti_melahirkan'))
                                    ->orderBy('id')
                                    ->pluck('nama', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->helperText(function (Get $get): ?string {
                                $jenisId = $get('jenis_izin_id');
                                if (! $jenisId) {
                                    return null;
                                }

                                $jenis = JenisIzin::find($jenisId);
                                if (! $jenis || $jenis->kategori !== 'cuti') {
                                    return null;
                                }

                                $targetUserId = (int) ($get('user_id') ?: Auth::id());
                                $tahun = now()->year;
                                $kuota = (int) ($jenis->kuota_hari ?: 12);
                                $terpakai = 0;

                                if ($targetUserId > 0) {
                                    $riwayatCuti = PengajuanIzin::query()
                                        ->where('user_id', $targetUserId)
                                        ->where('jenis_izin_id', $jenis->id)
                                        ->where('status', 'disetujui')
                                        ->whereYear('tanggal_mulai', $tahun)
                                        ->get(['tanggal_mulai', 'tanggal_selesai']);

                                    foreach ($riwayatCuti as $item) {
                                        $period = CarbonPeriod::create(
                                            Carbon::parse($item->tanggal_mulai),
                                            Carbon::parse($item->tanggal_selesai)
                                        );
                                        foreach ($period as $date) {
                                            if ($date->year === $tahun && ! HariLibur::isHariLibur($date)) {
                                                $terpakai++;
                                            }
                                        }
                                    }
                                }

                                $sisa = max(0, $kuota - $terpakai);
                                $info = "Sisa kuota {$jenis->nama} tahun {$tahun}: {$sisa} dari {$kuota} hari kerja (di luar hari libur & cuti bersama).";

                                if ($jenis->kode === 'cuti_alasan_penting') {
                                    $info .= ' PNS Laki-laki pendampingan istri melahirkan: maks. 1 bulan tanpa potong Cuti Tahunan (wajib surat rawat inap RS/klinik). PPPK Laki-laki: menggunakan Cuti Tahunan (maks. 6 hari kerja).';
                                }

                                return $info;
                            })
                            ->required(),

                        DatePicker::make('tanggal_mulai')
                            ->label('Dari Tanggal')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->default(now())
                            ->live()
                            ->required(),

                        DatePicker::make('tanggal_selesai')
                            ->label('Sampai Tanggal')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->default(now())
                            ->live()
                            ->helperText(function (Get $get): ?string {
                                $mulai = $get('tanggal_mulai');
                                $selesai = $get('tanggal_selesai');
                                if (! $mulai || ! $selesai) {
                                    return null;
                                }

                                $start = Carbon::parse($mulai)->startOfDay();
                                $end = Carbon::parse($selesai)->startOfDay();

                                if ($end->lt($start)) {
                                    return 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
                                }

                                $hariKerja = 0;
                                foreach (CarbonPeriod::create($start, $end) as $date) {
                                    if (! HariLibur::isHariLibur($date)) {
                                        $hariKerja++;
                                    }
                                }

                                return "Durasi yang diambil: {$hariKerja} hari kerja efektif (di luar akhir pekan, libur nasional & cuti bersama).";
                            })
                            ->required(),

                        Textarea::make('alasan')
                            ->label('Alasan / Keterangan')
                            ->placeholder('Jelaskan alasan izin / sakit / cuti...')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        FileUpload::make('lampiran')
                            ->label('Surat Keterangan Dokter (Wajib untuk Sakit)')
                            ->disk('public')
                            ->directory('lampiran_izin')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->rules(['file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'])
                            ->maxSize(1024)
                            ->visible(function (Get $get): bool {
                                $jenis = JenisIzin::find($get('jenis_izin_id'));
                                return $jenis && $jenis->kategori === 'sakit' && (bool) $jenis->butuh_lampiran;
                            })
                            ->required(function (Get $get): bool {
                                $jenis = JenisIzin::find($get('jenis_izin_id'));
                                return $jenis && $jenis->kategori === 'sakit' && (bool) $jenis->butuh_lampiran;
                            })
                            ->helperText('Khusus izin sakit. Maksimal ukuran file 1MB (PDF, JPG, PNG).')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Persetujuan / Verifikasi Atasan')
                    ->hiddenOn('create')
                    ->schema([
                        Select::make('status')
                            ->label('Status Persetujuan')
                            ->options([
                                'menunggu' => 'Menunggu Persetujuan',
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                            ])
                            ->default('menunggu')
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Select::make('disetujui_oleh')
                            ->label('Diverifikasi Oleh')
                            ->relationship('approver', 'name')
                            ->placeholder('Belum diverifikasi')
                            ->disabled()
                            ->dehydrated(),

                        Textarea::make('catatan_atasan')
                            ->label('Catatan Atasan')
                            ->rows(2)
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
