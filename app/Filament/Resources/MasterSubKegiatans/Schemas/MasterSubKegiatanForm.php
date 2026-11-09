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
                        ->placeholder('Pilih Pejabat Fungsional (Opsional)')
                        ->options(function (Get $get, ?MasterSubKegiatan $record, $livewire) {
                            /** @var \App\Models\User|null $user */
                            $user = auth()->user();
                            $userBidangId = $user?->bidang_id ?: ($user ? \App\Models\Bidang::where('kepala_id', $user->id)->value('id') : null);

                            $record = $record ?? ($livewire instanceof \Filament\Resources\Pages\EditRecord ? $livewire->getRecord() : null);
                            $kegiatanId = $get('kegiatan_id') ?? $record?->kegiatan_id;
                            $kegiatanBidangId = $kegiatanId ? MasterKegiatan::find($kegiatanId)?->bidang_id : $record?->kegiatan?->bidang_id;

                            // Prioritaskan bidang dari user aktif jika user terikat bidang (PPTK/Staff), jika Super Admin ambil dari kegiatan induk
                            $targetBidangId = $userBidangId ?: $kegiatanBidangId;

                            return User::query()
                                ->where('aktif', true)
                                ->when($targetBidangId, fn ($q) => $q->where('bidang_id', $targetBidangId))
                                ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['pa', 'ppk']))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all();
                        })
                        ->searchable()
                        ->preload()
                        ->helperText($disabled ? null : 'Hanya menampilkan pegawai di bidang yang sama dengan kegiatan.')
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
                        Action::make('toggle_lock')
                            ->label(fn (?MasterSubKegiatan $record): string => $record?->is_locked ? 'Buka Kunci RKA' : 'Kunci RKA')
                            ->icon(fn (?MasterSubKegiatan $record): string => $record?->is_locked ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                            ->color(fn (?MasterSubKegiatan $record): string => $record?->is_locked ? 'success' : 'danger')
                            ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false)
                            ->requiresConfirmation()
                            ->modalHeading(fn (?MasterSubKegiatan $record): string => $record?->is_locked ? 'Buka Kunci RKA Sub Kegiatan?' : 'Kunci RKA Sub Kegiatan?')
                            ->modalDescription(fn (?MasterSubKegiatan $record): string => $record?->is_locked
                                ? 'Membuka kunci memungkinkan penambahan item belanja, penyesuaian harga satuan, volume anggaran, dan pergeseran nomenklatur sub kegiatan.'
                                : 'Mengunci RKA akan memproteksi seluruh rincian belanja dan identitas sub kegiatan dari perubahan yang tidak disengaja sesuai DPA resmi.')
                            ->action(function (?MasterSubKegiatan $record, $livewire): void {
                                if ($record) {
                                    $newStatus = ! (bool) $record->is_locked;
                                    $record->update(['is_locked' => $newStatus]);
                                    Notification::make()
                                        ->title($newStatus ? 'RKA Berhasil Dikunci (Proteksi DPA Aktif)' : 'Kunci RKA Berhasil Dibuka (Mode Perubahan Aktif)')
                                        ->color($newStatus ? 'danger' : 'success')
                                        ->send();
                                    $livewire->redirect(MasterSubKegiatanResource::getUrl('edit', ['record' => $record]));
                                }
                            }),

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

