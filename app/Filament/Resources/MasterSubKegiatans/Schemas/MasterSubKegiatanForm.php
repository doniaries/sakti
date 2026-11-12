<?php

namespace App\Filament\Resources\MasterSubKegiatans\Schemas;

use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Models\MasterKegiatan;
use App\Models\MasterSubKegiatan;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MasterSubKegiatanForm
{
    /**
     * Reusable fields untuk Form Edit & Modal SlideOver (DRY)
     *
     * @return array<\Filament\Schemas\Components\Component|\Filament\Forms\Components\Component>
     */
    public static function getFields(bool $disabled = false): array
    {
        $currentYear = \App\Models\PengaturanAplikasi::getTahunAnggaranAktif();
        $years = [];
        for ($y = $currentYear + 1; $y >= $currentYear - 2; $y--) {
            $years[$y] = (string) $y;
        }

        return [
            Grid::make(['default' => 1, 'md' => 12])
                ->schema([
                    // Baris 1: Kode Sub Kegiatan (3), Tahun Anggaran (2), Kegiatan Induk (7)
                    TextInput::make('kode_sub_kegiatan')
                        ->label('Kode Sub Kegiatan')
                        ->mask('9.99.99.9.99.9999')
                        ->disabled($disabled)
                        ->required()
                        ->columnSpan(['default' => 12, 'md' => 3]),

                    Select::make('tahun_anggaran')
                        ->label('Tahun Anggaran')
                        ->options($years)
                        ->default(fn () => (int) (request()->query('tahun') ?? $currentYear))
                        ->disabled($disabled)
                        ->required()
                        ->columnSpan(['default' => 12, 'md' => 2]),

                    Select::make('kegiatan_id')
                        ->label('Kegiatan Induk')
                        ->relationship('kegiatan', 'nama_kegiatan')
                        ->getOptionLabelFromRecordUsing(fn (MasterKegiatan $record) => "[{$record->kode_kegiatan}] {$record->nama_kegiatan}")
                        ->searchable()
                        ->preload()
                        ->live()
                        ->disabled($disabled)
                        ->required()
                        ->columnSpan(['default' => 12, 'md' => 7]),

                    // Baris 2: Nomenklatur Sub Kegiatan (8), Penanggung Jawab JF (4)
                    Textarea::make('nama_sub_kegiatan')
                        ->label('Nomenklatur Sub Kegiatan')
                        ->disabled($disabled)
                        ->extraInputAttributes(['style' => 'text-transform: uppercase; font-weight: 600;'])
                        ->rows(2)
                        ->required()
                        ->columnSpan(['default' => 12, 'md' => 8]),

                    Select::make('jf_id')
                        ->label('Penanggung Jawab (JF)')
                        ->placeholder('Pilih Pejabat Fungsional (JF)')
                        ->options(function (Get $get, ?MasterSubKegiatan $record, $livewire) {
                            $record = $record ?? ($livewire instanceof \Filament\Resources\Pages\EditRecord ? $livewire->getRecord() : null);
                            $kegiatanId = $get('kegiatan_id') ?? $record?->kegiatan_id;
                            $targetBidangId = $kegiatanId
                                ? MasterKegiatan::find($kegiatanId)?->bidang_id
                                : $record?->kegiatan?->bidang_id;

                            $query = User::query()
                                ->where('aktif', true)
                                ->whereIn('eselon', [\App\Enums\Eselon::IV_A, \App\Enums\Eselon::IV_B, 'IV.a', 'IV.b'])
                                ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['pa', 'ppk']));

                            $hasInBidang = (clone $query)->where('bidang_id', $targetBidangId)->exists();
                            if ($hasInBidang) {
                                $query->where('bidang_id', $targetBidangId);
                            }

                            return $query->orderBy('name')
                                ->get()
                                ->mapWithKeys(function (User $u) {
                                    $eselonVal = $u->eselon instanceof \App\Enums\Eselon ? $u->eselon->value : (string) $u->eselon;
                                    $jabatan = $u->jabatan?->nama ? " - {$u->jabatan->nama}" : '';
                                    return [$u->id => "{$u->name} (Eselon {$eselonVal}{$jabatan})"];
                                })
                                ->all();
                        })
                        ->searchable()
                        ->preload()
                        ->helperText($disabled ? null : 'Hanya menampilkan Pejabat Fungsional (JF) Eselon IV (mengecualikan Eselon III/Kabid dan staf biasa).')
                        ->disabled($disabled)
                        ->columnSpan(['default' => 12, 'md' => 4]),
                ]),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Sub Kegiatan')
                    ->description(function (?MasterSubKegiatan $record): string {
                        if ($record?->is_locked) {
                            return '🔒 Status RKA: TERKUNCI (Baku Sesuai DPA/RKA). Data rincian belanja & identitas sub kegiatan terlindungi dari perubahan.';
                        }
                        return '🔓 Status RKA: TERBUKA (Mode Perubahan / Pergeseran Anggaran). Anda dapat mengubah nama kegiatan, harga satuan, dan volume.';
                    })
                    ->compact()
                    ->columnSpanFull()
                    ->headerActions([
                        Action::make('ubah_sub_kegiatan')
                            ->label('Ubah Sub Kegiatan / Pergeseran')
                            ->icon('heroicon-o-pencil-square')
                            ->color('warning')
                            ->slideOver()
                            ->modalWidth('4xl')
                            ->visible(fn (?MasterSubKegiatan $record): bool => ! (bool) ($record?->is_locked))
                            ->modalHeading(fn (?MasterSubKegiatan $record): string => 'Ubah Sub Kegiatan: ' . ($record?->kode_sub_kegiatan ?? ''))
                            ->modalDescription('Perbarui data identitas Sub Kegiatan & penugasan JF via SlideOver.')
                            ->fillForm(fn (?MasterSubKegiatan $record): array => [
                                'kode_sub_kegiatan' => $record?->kode_sub_kegiatan,
                                'tahun_anggaran' => $record?->tahun_anggaran,
                                'kegiatan_id' => $record?->kegiatan_id,
                                'jf_id' => $record?->jf_id,
                                'nama_sub_kegiatan' => $record?->nama_sub_kegiatan,
                            ])
                            ->form(static::getFields(disabled: false))
                            ->action(function (array $data, ?MasterSubKegiatan $record, $livewire): void {
                                if ($record) {
                                    $record->update($data);
                                    Notification::make()
                                        ->title('Identitas Sub Kegiatan berhasil diperbarui')
                                        ->success()
                                        ->send();
                                    $livewire->redirect(MasterSubKegiatanResource::getUrl('edit', ['record' => $record]));
                                }
                            }),
                    ])
                    ->schema(static::getFields(disabled: true)),
            ]);
    }
}

