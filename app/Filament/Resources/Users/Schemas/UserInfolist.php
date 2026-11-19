<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // Header Identitas Utama Pegawai
                Section::make('Identitas Pegawai')
                    ->icon('heroicon-o-identification')
                    ->headerActions([
                        Action::make('quick_account')
                            ->label('Akun & Password')
                            ->icon('heroicon-m-key')
                            ->color('info')
                            ->button()
                            ->visible(fn($record): bool => (\Illuminate\Support\Facades\Auth::user()?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian']) ?? false) || \Illuminate\Support\Facades\Auth::id() === $record->id)
                            ->modalHeading('Ubah Nama, Email & Password')
                            ->modalDescription('Perbarui nama lengkap, email login, atau password pegawai secara langsung.')
                            ->modalWidth('md')
                            ->fillForm(fn($record): array => [
                                'name' => $record->name,
                                'email' => $record->email,
                            ])
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                    ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('Email Login')
                                    ->email()
                                    ->required()
                                    ->unique('users', 'email', ignoreRecord: true)
                                    ->maxLength(255),
                                TextInput::make('password')
                                    ->label('Password Baru')
                                    ->password()
                                    ->revealable()
                                    ->placeholder('Kosongkan jika tidak diubah...')
                                    ->rules(['nullable', 'min:6'])
                                    ->maxLength(255),
                            ])
                            ->action(function ($record, array $data): void {
                                $payload = [
                                    'name' => $data['name'],
                                    'email' => $data['email'],
                                ];

                                if (filled($data['password'] ?? null)) {
                                    $payload['password'] = Hash::make($data['password']);
                                }

                                $record->update($payload);

                                Notification::make()
                                    ->title('Data akun & password berhasil diperbarui')
                                    ->success()
                                    ->send();
                            }),
                        Action::make('edit')
                            ->label('Ubah')
                            ->icon('heroicon-m-pencil-square')
                            ->color('warning')
                            ->button()
                            ->visible(fn($record): bool => UserResource::canEdit($record))
                            ->url(fn($record) => UserResource::getUrl('edit', ['record' => $record])),
                    ])
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 5,
                        ])
                            ->schema([
                                ImageEntry::make('avatar_url')
                                    ->label('Foto Profil')
                                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=0D8ABC&color=fff')
                                    ->circular()
                                    ->disk('public')
                                    ->extraImgAttributes(['class' => 'cursor-pointer hover:opacity-90 transition'])
                                    ->action(
                                        Action::make('zoom_foto_profil')
                                            ->modalHeading(fn(User $record): string => 'Foto Profil — ' . $record->name)
                                            ->modalSubmitAction(false)
                                            ->modalCancelActionLabel('Tutup')
                                            ->modalContent(function (User $record): \Illuminate\Support\HtmlString {
                                                $url = $record->avatar_url
                                                    ? asset('storage/' . ltrim((string) $record->avatar_url, '/'))
                                                    : 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=0D8ABC&color=fff&size=512';

                                                return new \Illuminate\Support\HtmlString(
                                                    '<div class="flex justify-center"><img src="' . e($url) . '" alt="' . e($record->name) . '" class="max-h-[75vh] w-auto rounded-xl object-contain shadow-lg" /></div>'
                                                );
                                            })
                                    )
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 1,
                                    ]),

                                Group::make([
                                    TextEntry::make('nama_lengkap_dan_gelar')
                                        ->label('Nama Lengkap & Gelar')
                                        ->state(function ($record): string {
                                            $nama = trim((string) $record->name);
                                            $namaLower = mb_strtolower($nama, 'UTF-8');
                                            $depan = trim((string) ($record->gelar_depan ?? ''));
                                            $belakang = trim((string) ($record->gelar_belakang ?? ''));

                                            if ($depan !== '' && ! str_starts_with($namaLower, mb_strtolower($depan, 'UTF-8'))) {
                                                $nama = "{$depan} {$nama}";
                                            }
                                            if ($belakang !== '' && ! str_contains($namaLower, mb_strtolower($belakang, 'UTF-8'))) {
                                                $nama = "{$nama}, {$belakang}";
                                            }

                                            return $nama;
                                        })
                                        ->size(TextSize::Large)
                                        ->weight('bold')
                                        ->icon('heroicon-m-user')
                                        ->color('primary'),

                                    Grid::make([
                                        'default' => 1,
                                        'sm' => 2,
                                        'lg' => 3,
                                    ])
                                        ->schema([
                                            TextEntry::make('nip')
                                                ->label('NIP')
                                                ->icon('heroicon-m-hashtag')
                                                ->copyable()
                                                ->tooltip('Klik untuk menyalin NIP')
                                                ->helperText('Klik untuk menyalin')
                                                ->weight('medium'),

                                            TextEntry::make('email')
                                                ->label('Email Login')
                                                ->icon('heroicon-m-envelope')
                                                ->copyable()
                                                ->tooltip('Klik untuk menyalin Email')
                                                ->helperText('Klik untuk menyalin')
                                                ->weight('medium')
                                                ->placeholder('-'),

                                            TextEntry::make('bidang.nama')
                                                ->label('Bidang')
                                                ->icon('heroicon-m-building-office')
                                                ->badge()
                                                ->color('primary')
                                                ->tooltip('Klik untuk melihat daftar pegawai di bidang ini')
                                                ->url(function ($record): ?string {
                                                    if (! $record->bidang_id) {
                                                        return null;
                                                    }

                                                    /** @var \App\Models\User|null $authUser */
                                                    $authUser = \Illuminate\Support\Facades\Auth::user();

                                                    if ($authUser && ! $authUser->canViewAllBidangKepegawaian() && ! $authUser->isPptk()) {
                                                        return UserResource::getUrl('index', ['tab' => 'bidang']);
                                                    }

                                                    return UserResource::getUrl('index', ['tab' => $record->bidang?->slug]);
                                                })
                                                ->visible(fn($record): bool => filled($record->bidang_id)),

                                            TextEntry::make('atasan.name')
                                                ->label('Atasan Langsung')
                                                ->icon('heroicon-m-user-circle')
                                                ->weight('medium')
                                                ->placeholder('Pimpinan Tertinggi (Tidak Ada Atasan Langsung)'),

                                            TextEntry::make('roles.name')
                                                ->label('Role / Hak Akses')
                                                ->icon('heroicon-m-shield-check')
                                                ->badge()
                                                ->color('primary')
                                                ->placeholder('-'),

                                            TextEntry::make('jfPenempatan.name')
                                                ->label('Penempatan di Bawah JF')
                                                ->icon('heroicon-m-user-group')
                                                ->badge()
                                                ->color('success')
                                                ->visible(fn($record): bool => filled($record->jf_id)),

                                            TextEntry::make('nik')
                                                ->label('NIK')
                                                ->icon('heroicon-m-credit-card')
                                                ->formatStateUsing(fn(?string $state): ?string => $state && str_contains(strtoupper($state), 'E+') ? number_format((float) $state, 0, '', '') : $state)
                                                ->copyable()
                                                ->placeholder('-'),

                                            TextEntry::make('no_kk')
                                                ->label('Nomor KK')
                                                ->icon('heroicon-m-identification')
                                                ->copyable()
                                                ->placeholder('-'),

                                            TextEntry::make('agama')
                                                ->label('Agama')
                                                ->icon('heroicon-m-bookmark')
                                                ->placeholder('-'),

                                            TextEntry::make('no_telp')
                                                ->label('Nomor Telepon')
                                                ->icon('heroicon-m-phone')
                                                ->placeholder('-'),

                                            TextEntry::make('npwp')
                                                ->label('Nomor NPWP')
                                                ->icon('heroicon-m-wallet')
                                                ->copyable()
                                                ->placeholder('-'),

                                            TextEntry::make('tanggal_npwp')
                                                ->label('Tanggal NPWP')
                                                ->date('d/m/Y')
                                                ->placeholder('-'),

                                            TextEntry::make('no_bpjs')
                                                ->label('Nomor BPJS')
                                                ->icon('heroicon-m-shield-check')
                                                ->copyable()
                                                ->placeholder('-'),

                                            TextEntry::make('no_taspen')
                                                ->label('Nomor Taspen')
                                                ->icon('heroicon-m-building-library')
                                                ->copyable()
                                                ->placeholder('-'),

                                            TextEntry::make('jabatan.nama')
                                                ->label('Jabatan Saat Ini')
                                                ->weight('bold')
                                                ->placeholder('Belum Ditentukan'),

                                            TextEntry::make('eselon')
                                                ->label('Eselon Jabatan')
                                                ->badge()
                                                ->color('warning')
                                                ->placeholder('Non Eselon'),

                                            TextEntry::make('status_kerja')
                                                ->label('Tipe Kerja')
                                                ->badge()
                                                ->color(fn ($state) => strtolower($state ?? 'wfo') === 'wfh' ? 'warning' : 'success')
                                                ->formatStateUsing(fn ($state) => strtoupper($state ?? 'WFO')),

                                            TextEntry::make('jadwalKerja.nama')
                                                ->label('Shift / Jadwal Kerja')
                                                ->badge()
                                                ->color('info')
                                                ->placeholder('Jadwal Default Sistem'),

                                            TextEntry::make('jabatan_tim_kreatif')
                                                ->label('Tim Kreatif Dinas')
                                                ->badge()
                                                ->placeholder('Bukan Anggota Tim Kreatif'),

                                            TextEntry::make('alamat')
                                                ->label('Alamat Domisili')
                                                ->icon('heroicon-m-home')
                                                ->placeholder('-')
                                                ->columnSpanFull(),
                                        ]),
                                ])
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 4,
                                    ]),
                            ]),
                    ]),

                // Tab Navigasi MyASN
                Actions::make([
                    Action::make('ubah_data_pegawai')
                        ->label('Ubah Data Pegawai')
                        ->icon('heroicon-o-pencil-square')
                        ->color('primary')
                        ->url(fn(User $record): string => UserResource::getUrl('edit', ['record' => $record]))
                        ->visible(function (User $record): bool {
                            /** @var User|null $authUser */
                            $authUser = Auth::user();

                            return $authUser?->can('update', $record) ?? false;
                        }),
                ])->alignEnd(),

                Section::make('Jabatan, Penugasan & Jadwal Kerja')
                    ->icon('heroicon-o-briefcase')
                    ->columns(['default' => 2, 'md' => 5])
                    ->schema([
                        TextEntry::make('jabatan.nama')->label('Jabatan Saat Ini')->weight('bold')->placeholder('Belum Ditentukan'),
                        TextEntry::make('eselon')->label('Eselon Jabatan')->badge()->color('warning')->placeholder('Non Eselon'),
                        TextEntry::make('status_kerja')->label('Tipe Kerja')->badge()->color(fn($state) => strtolower($state ?? 'wfo') === 'wfh' ? 'warning' : 'success')->formatStateUsing(fn($state) => strtoupper($state ?? 'WFO')),
                        TextEntry::make('jadwalKerja.nama')->label('Shift / Jadwal Kerja')->badge()->color('info')->placeholder('Jadwal Default Sistem'),
                        TextEntry::make('jabatan_tim_kreatif')->label('Tim Kreatif Dinas')->badge()->placeholder('Bukan Anggota Tim Kreatif'),
                    ]),
            ]);
    }

    private static function pdfAttachmentEntry(string $attribute, string $label): Actions
    {
        return Actions::make([
            Action::make('lihat_' . $attribute)
                ->label('Lihat Berkas')
                ->icon('heroicon-o-eye')
                ->button()
                ->size('sm')
                ->color('primary')
                ->modalHeading($label)
                ->modalWidth(\Filament\Support\Enums\Width::FiveExtraLarge)
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->visible(fn($record): bool => filled($record->{$attribute} ?? null))
                ->modalContent(function ($record) use ($attribute): \Illuminate\Support\HtmlString {
                    $path = (string) ($record->{$attribute} ?? '');
                    $url = asset('storage/' . ltrim($path, '/'));

                    return new \Illuminate\Support\HtmlString(
                        '<iframe src="' . e($url) . '" class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white" style="height: 75vh;"></iframe>'
                    );
                })
                ->extraModalFooterActions([
                    Action::make('download_' . $attribute)
                        ->label('Unduh PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('danger')
                        ->action(function ($record) use ($attribute) {
                            $path = (string) ($record->{$attribute} ?? '');
                            if ($path === '' || ! Storage::disk('public')->exists($path)) {
                                Notification::make()
                                    ->title('Berkas PDF tidak ditemukan di server.')
                                    ->danger()
                                    ->send();

                                return null;
                            }

                            return response()->download(Storage::disk('public')->path($path));
                        }),
                ]),
        ]);
    }

    private static function isKontrak(mixed $record): bool
    {
        $status = $record?->status_kepegawaian ?? null;
        $value = $status instanceof \App\Enums\StatusKepegawaian
            ? $status->value
            : (string) $status;

        return $value === \App\Enums\StatusKepegawaian::KONTRAK->value;
    }

    /**
     * @return array<string, mixed>
     */
    private static function disabledTabAttributes(mixed $record): array
    {
        if (! self::isKontrak($record)) {
            return [];
        }

        return [
            'disabled' => true,
            'aria-disabled' => 'true',
            'class' => 'opacity-40 pointer-events-none cursor-not-allowed',
            'title' => 'Tidak berlaku untuk Pegawai Kontrak / Non-ASN',
        ];
    }
}
