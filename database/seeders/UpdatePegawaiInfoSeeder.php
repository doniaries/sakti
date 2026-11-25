<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Eselon;
use App\Enums\JenisKelamin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class UpdatePegawaiInfoSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/pegawai_80.json');
        if (! file_exists($path)) {
            $this->command?->warn('File pegawai_80.json tidak ditemukan.');

            return;
        }

        $items = json_decode((string) file_get_contents($path), true);
        if (! is_array($items)) {
            return;
        }

        $updated = 0;

        foreach ($items as $item) {
            $nip = trim((string) ($item['nip'] ?? ''));
            $nama = trim((string) ($item['nama'] ?? ''));

            $user = $nip !== ''
                ? User::where('nip', $nip)->first()
                : null;

            if (! $user && $nama !== '') {
                $user = User::where('name', $nama)->first();
            }

            if (! $user) {
                continue;
            }

            $dirty = false;

            if (blank($user->tempat_lahir) && filled($item['tempat_lahir'] ?? null)) {
                $user->tempat_lahir = $item['tempat_lahir'];
                $dirty = true;
            }

            if (blank($user->tanggal_lahir) && filled($item['tanggal_lahir'] ?? null)) {
                try {
                    $user->tanggal_lahir = Carbon::parse($item['tanggal_lahir'])->toDateString();
                    $dirty = true;
                } catch (\Throwable) {
                    // Abaikan format tanggal yang tidak valid
                }
            }

            if (blank($user->jenis_kelamin) && filled($item['jk'] ?? null)) {
                $jk = strtoupper((string) $item['jk']);
                $user->jenis_kelamin = $jk === 'L' ? JenisKelamin::LAKI_LAKI : JenisKelamin::PEREMPUAN;
                $dirty = true;
            }

            if (blank($user->eselon) && filled($item['eselon'] ?? null)) {
                $eselon = Eselon::tryFrom((string) $item['eselon']);
                if ($eselon) {
                    $user->eselon = $eselon;
                    $dirty = true;
                }
            }

            if (blank($user->pangkat_golongan) && filled($item['pangkat_golongan'] ?? null)) {
                $user->pangkat_golongan = $item['pangkat_golongan'];
                $dirty = true;
            }

            if ($dirty) {
                $user->save();
                $updated++;
            }
        }

        $this->command?->info("UpdatePegawaiInfoSeeder selesai: {$updated} pegawai diperbarui.");
    }
}
