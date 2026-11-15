<?php

namespace Database\Seeders;

use App\Enums\StatusKepegawaian;
use App\Models\Bidang;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class StrukturOrganisasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $penempatanPath = database_path('seeders/penempatan_pegawai.json');
        if (! file_exists($penempatanPath)) {
            $this->command?->warn('File penempatan_pegawai.json tidak ditemukan!');
            return;
        }

        $data = json_decode(file_get_contents($penempatanPath), true);

        // 1. Pastikan 4 Bidang resmi tersedia
        $bidangBySlug = [];
        foreach ($data['bidangs'] ?? [] as $b) {
            $bidang = Bidang::updateOrCreate(
                ['slug' => $b['slug']],
                ['nama' => $b['nama']]
            );
            $bidangBySlug[$b['slug']] = $bidang;
        }

        // 2. Petakan seluruh User berdasarkan email
        $usersByEmail = User::query()->get()->keyBy(fn (User $u) => strtolower((string) $u->email));
        $hasStatusKepegawaian = Schema::hasColumn('users', 'status_kepegawaian');

        // 3. Tetapkan bidang_id, jabatan_id, anggaran_role, dan atasan/jf pada setiap pegawai
        foreach ($data['users'] ?? [] as $row) {
            $emailKey = strtolower((string) ($row['email'] ?? ''));
            $user = $usersByEmail->get($emailKey)
                ?? (! empty($row['nip']) ? User::query()->where('nip', $row['nip'])->first() : null);

            if (! $user) {
                continue;
            }

            $bidangId = ! empty($row['bidang_slug']) && isset($bidangBySlug[$row['bidang_slug']])
                ? $bidangBySlug[$row['bidang_slug']]->id
                : null;

            $atasanId = ! empty($row['atasan_email'])
                ? $usersByEmail->get(strtolower($row['atasan_email']))?->id
                : null;

            $jfId = ! empty($row['jf_email'])
                ? $usersByEmail->get(strtolower($row['jf_email']))?->id
                : null;

            $jabatanId = ! empty($row['jabatan'])
                ? Jabatan::query()->whereRaw('LOWER(nama) = ?', [strtolower($row['jabatan'])])->value('id')
                : $user->jabatan_id;

            $updateData = [
                'bidang_id' => $bidangId,
                'atasan_id' => $atasanId,
                'jf_id' => $jfId,
                'jabatan_id' => $jabatanId,
                'anggaran_role' => $row['anggaran_role'] ?? null,
            ];

            if ($hasStatusKepegawaian) {
                $updateData['status_kepegawaian'] = $row['status_kepegawaian']
                    ?? ($emailKey === 'superadmin@gmail.com' ? StatusKepegawaian::PNS->value : StatusKepegawaian::detectFromNip($row['nip'] ?? null)->value);
            }

            $user->update($updateData);

            if (! empty($row['roles'])) {
                $user->syncRoles($row['roles']);
            }
        }

        // 4. Tetapkan kepala_id pada masing-masing Bidang
        foreach ($data['bidangs'] ?? [] as $b) {
            $bidang = $bidangBySlug[$b['slug']] ?? null;
            $kepalaId = ! empty($b['kepala_email'])
                ? $usersByEmail->get(strtolower($b['kepala_email']))?->id
                : null;

            if ($bidang && $kepalaId) {
                $bidang->update(['kepala_id' => $kepalaId]);
            }
        }
    }
}
