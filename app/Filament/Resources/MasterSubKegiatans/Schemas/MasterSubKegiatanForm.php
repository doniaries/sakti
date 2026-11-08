<?php

namespace App\Filament\Resources\MasterSubKegiatans\Schemas;

use App\Models\MasterKegiatan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MasterSubKegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        $currentYear = (int) date('Y');
        $maxYear = $currentYear + 1; // Batasi maksimal ke 2027 jika tahun sekarang 2026
        $minYear = $currentYear - 2;

        $years = [];
        for ($y = $maxYear; $y >= $minYear; $y--) {
            $years[$y] = (string) $y;
        }

        return $schema
            ->components([
                Select::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->options($years)
                    ->default(fn () => (int) (request()->query('tahun') ?? $currentYear))
                    ->searchable()
                    ->required(),

                Select::make('kegiatan_id')
                    ->label('Kegiatan Induk')
                    ->relationship('kegiatan', 'nama_kegiatan', function ($query) {
                        /** @var \App\Models\User|null $user */
                        $user = \Illuminate\Support\Facades\Auth::user();
                        if ($user && $user->hasRole('jf') && ! $user->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara', 'pptk', 'pimpinan']) && $user->bidang_id) {
                            $query->where('bidang_id', $user->bidang_id);
                        }
                    })
                    ->getOptionLabelFromRecordUsing(fn (MasterKegiatan $record) => $record->label)
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        if ($state) {
                            $kegiatan = MasterKegiatan::find($state);
                            if ($kegiatan) {
                                $currentCode = (string) $get('kode_sub_kegiatan');
                                if (blank($currentCode) || ! str_starts_with($currentCode, $kegiatan->kode_kegiatan)) {
                                    $set('kode_sub_kegiatan', $kegiatan->kode_kegiatan);
                                }

                                // Reset jf_id jika JF yang terpilih sebelumnya tidak berada di bidang kegiatan yang baru
                                $currentJfId = $get('jf_id');
                                if ($currentJfId) {
                                    $jfUser = \App\Models\User::find($currentJfId);
                                    if ($jfUser && (int) $jfUser->bidang_id !== (int) $kegiatan->bidang_id) {
                                        $set('jf_id', null);
                                    }
                                }
                            }
                        } else {
                            $set('jf_id', null);
                        }
                    })
                    ->required(),

                Select::make('jf_id')
                    ->label('Pejabat Fungsional (JF) Pengampu')
                    ->placeholder('Pilih Pejabat Fungsional (JF)')
                    ->helperText(fn (Get $get) => $get('kegiatan_id')
                        ? 'Hanya menampilkan Pejabat Fungsional (JF) yang berada di bidang bersangkutan.'
                        : 'Pilih Kegiatan Induk terlebih dahulu agar daftar JF terfilter sesuai bidang.')
                    ->default(function () {
                        /** @var \App\Models\User|null $user */
                        $user = \Illuminate\Support\Facades\Auth::user();
                        return $user && $user->hasRole('jf') ? $user->id : null;
                    })
                    ->options(function (Get $get, ?\App\Models\MasterSubKegiatan $record) {
                        $kegiatanId = $get('kegiatan_id') ?? $record?->kegiatan_id;
                        if (! $kegiatanId) {
                            return [];
                        }

                        $bidangId = MasterKegiatan::find($kegiatanId)?->bidang_id;
                        if (! $bidangId) {
                            return [];
                        }

                        $query = \App\Models\User::query()
                            ->where('aktif', true)
                            ->where('bidang_id', $bidangId)
                            ->whereHas('roles', fn ($q) => $q->where('name', 'jf'));

                        if ($record?->jf_id) {
                            $query->orWhere('id', $record->jf_id);
                        }

                        return $query->orderBy('name')
                            ->get()
                            ->mapWithKeys(function ($u) {
                                return [$u->id => "{$u->name} (JF • {$u->bidang?->nama})"];
                            })
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->nullable(),

                TextInput::make('kode_sub_kegiatan')
                    ->label('Kode Sub Kegiatan')
                    ->placeholder('Contoh: 3.26.04.2.02.0014')
                    ->mask('9.99.99.9.99.9999')
                    ->maxLength(50)
                    ->helperText('Format titik otomatis sesuai standar SIPD (contoh: 3.26.04.2.02.0014)')
                    ->required(),

                TextInput::make('nama_sub_kegiatan')
                    ->label('Nomenklatur Sub Kegiatan')
                    ->placeholder('Contoh: Fasilitasi penguatan Kekayaan Intelektual')
                    ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
