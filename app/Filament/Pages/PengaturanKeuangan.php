<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\PengaturanAplikasi;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use UnitEnum;

class PengaturanKeuangan extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Pengaturan Keuangan';

    protected static ?string $title = 'Pengaturan Keuangan';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.pengaturan-keuangan';

    public int $tahun_anggaran_aktif;

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user !== null && $user->hasAnyRole([
            'super_admin',
            'admin',
            'pa',
            'ppk',
            'bendahara',
        ]);
    }

    public function mount(): void
    {
        $this->tahun_anggaran_aktif = PengaturanAplikasi::getTahunAnggaranAktif();
    }

    public function simpan(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara'])) {
            abort(403);
        }

        $this->validate([
            'tahun_anggaran_aktif' => 'required|integer|min:2020|max:2099',
        ]);

        $pengaturan = PengaturanAplikasi::first();
        if (! $pengaturan) {
            $pengaturan = PengaturanAplikasi::getPengaturan();
        }

        $pengaturan->update([
            'tahun_anggaran_aktif' => $this->tahun_anggaran_aktif,
        ]);

        Cache::forget('pengaturan_tahun_anggaran_aktif');

        Notification::make()
            ->title('Pengaturan Keuangan Berhasil Disimpan!')
            ->body("Tahun anggaran aktif sistem berhasil diperbarui ke {$this->tahun_anggaran_aktif}. Seluruh modul DPA, RKA, dan SPJ otomatis mengacu ke tahun ini.")
            ->success()
            ->send();
    }
}
