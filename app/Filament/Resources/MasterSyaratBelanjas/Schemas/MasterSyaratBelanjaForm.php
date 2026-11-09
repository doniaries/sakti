<?php

namespace App\Filament\Resources\MasterSyaratBelanjas\Schemas;

use App\Models\MasterSyaratBelanja;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MasterSyaratBelanjaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Syarat Dokumen Belanja')
                    ->description('Kelola berkas SOP yang wajib atau opsional dipenuhi staf pada saat pengajuan SPJ.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('metode_pengadaan')
                                    ->label('Metode Pengadaan')
                                    ->options([
                                        'non_katalog' => 'Non-Katalog (Pengadaan Langsung)',
                                        'e_katalog' => 'E-Katalog (E-Purchasing LKPP)',
                                        'semua' => 'Semua Metode (Lintas Pengadaan)',
                                    ])
                                    ->default('non_katalog')
                                    ->required(),

                                Select::make('jenis_belanja')
                                    ->label('Jenis Pekerjaan / Belanja')
                                    ->options(function (?string $state) {
                                        $options = [
                                            'barang_jasa' => 'Non-Katalog: Belanja Barang & Jasa',
                                            'makan_minum' => 'Non-Katalog: Makan & Minum Rapat',
                                            'sppd' => 'Non-Katalog: Perjalanan Dinas (SPPD)',
                                            'bbm' => 'Non-Katalog: BBM (Bahan Bakar Minyak)',
                                            'e_katalog' => 'E-Katalog: Pengadaan Paket E-Purchasing',
                                            'semua' => 'Dokumen Umum / Lintas Belanja',
                                        ];

                                        $customs = MasterSyaratBelanja::query()
                                            ->whereNotIn('jenis_belanja', array_keys($options))
                                            ->distinct()
                                            ->pluck('jenis_belanja');

                                        foreach ($customs as $c) {
                                            $options[$c] = 'Pekerjaan: ' . ucwords(str_replace('_', ' ', $c));
                                        }

                                        if ($state && ! isset($options[$state])) {
                                            $options[$state] = 'Pekerjaan: ' . ucwords(str_replace('_', ' ', $state));
                                        }

                                        return $options;
                                    })
                                    ->getOptionLabelUsing(function (?string $value): ?string {
                                        if (! $value) {
                                            return null;
                                        }

                                        return ucwords(str_replace('_', ' ', $value));
                                    })
                                    ->searchable()
                                    ->createOptionForm([
                                        TextInput::make('nama_baru')
                                            ->label('Nama Jenis Pekerjaan Baru')
                                            ->placeholder('Contoh: Pemeliharaan Gedung Kantor')
                                            ->required(),
                                    ])
                                    ->createOptionUsing(function (array $data): string {
                                        return Str::slug($data['nama_baru'], '_');
                                    })
                                    ->required(),
                            ]),

                        TextInput::make('nama_syarat')
                            ->label('Nama Dokumen Syarat')
                            ->placeholder('Contoh: Faktur / Nota Toko Asli')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('keterangan')
                            ->label('Petunjuk / Keterangan Dokumen (Opsional)')
                            ->placeholder('Contoh: Scan dokumen PDF asli bertandatangan dan berstempel')
                            ->rows(2),

                        Toggle::make('is_required')
                            ->label('Wajib Diunggah')
                            ->helperText('Jika diaktifkan, berkas ini menjadi syarat mutlak (wajib). Jika tidak, berkas bersifat opsional.')
                            ->default(true),
                    ]),
            ]);
    }
}
