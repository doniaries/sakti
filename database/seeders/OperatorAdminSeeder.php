<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class OperatorAdminSeeder extends Seeder
{
    /**
     * Membuat akun operator non-pegawai dengan role admin_kepegawaian dan admin_keuangan.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin_kepegawaian', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin_keuangan', 'guard_name' => 'web']);

        $operator = User::withTrashed()->updateOrCreate(
            ['email' => 'operator.sakti@sijunjung.go.id'],
            [
                'name' => 'Operator Kepegawaian & Keuangan',
                'password' => Hash::make('SaktiOperator2026!'),
                'nip' => null,
                'nomor_absensi' => null,
                'jabatan_id' => null,
                'bidang_id' => null,
                'aktif' => true,
            ]
        );

        if ($operator->trashed()) {
            $operator->restore();
        }

        // Sengaja tidak diberi role 'pegawai' agar tidak masuk daftar pegawai / absensi.
        $operator->syncRoles(['admin_kepegawaian', 'admin_keuangan']);

        $this->command?->info('Akun operator (operator.sakti@sijunjung.go.id) berhasil dibuat/disinkronkan dengan role admin_kepegawaian & admin_keuangan.');
    }
}
