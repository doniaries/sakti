<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class JenjangPendidikanSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        foreach ($users as $u) {
            $p = strtoupper($u->pendidikan ?? '');
            $jenjang = null;
            if (str_contains($p, 'S3') || str_contains($p, 'S-3')) {
                $jenjang = 'S3';
            } elseif (str_contains($p, 'S2') || str_contains($p, 'S-2')) {
                $jenjang = 'S2';
            } elseif (str_contains($p, 'S1') || str_contains($p, 'S-1') || str_contains($p, 'DIV') || str_contains($p, 'D IV')) {
                $jenjang = 'S1';
            } elseif (str_contains($p, 'D3') || str_contains($p, 'D-3') || str_contains($p, 'D III')) {
                $jenjang = 'D3';
            } elseif (str_contains($p, 'SMA') || str_contains($p, 'SMK') || str_contains($p, 'SLTA') || str_contains($p, 'ALIYAH') || str_contains($p, 'PAKET C')) {
                $jenjang = 'SLTA';
            } elseif (str_contains($p, 'SMP') || str_contains($p, 'SLTP') || str_contains($p, 'TSANAWIYAH')) {
                $jenjang = 'SMP';
            } elseif (str_contains($p, 'SD')) {
                $jenjang = 'SD';
            }

            if ($jenjang) {
                $u->update(['jenjang_pendidikan' => $jenjang]);
            }
        }
    }
}
