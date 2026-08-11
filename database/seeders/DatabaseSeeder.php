<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Panggil ShieldSeeder jika ada (digenerate oleh Filament Shield)
        if (class_exists('Database\Seeders\ShieldSeeder')) {
            $this->call('Database\Seeders\ShieldSeeder');
        }

        // 2. Buat Role
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);

        // 3. Beri Semua Permission ke Role Super Admin
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);

        // 3.5. Beri Permission ke Role Admin (Semua kecuali Role)
        $allPermissions = Permission::all();
        $adminPermissions = $allPermissions->filter(function ($permission) {
            return !str_contains($permission->name, 'role') && !str_contains($permission->name, 'permission');
        });
        $adminRole->syncPermissions($adminPermissions);

        // 4. Beri Permission ke Role Pegawai (Hanya Tertentu)
        $pegawaiPermissions = $allPermissions->filter(function ($permission) {
            // Pegawai hanya bisa melihat dan membuat absensi harian/mobile
            return str_contains($permission->name, 'absensi_harian') || str_contains($permission->name, 'absensi_mobile');
        });
        $pegawaiRole->syncPermissions($pegawaiPermissions);

        // 5. Buat Akun Super Admin
        $superAdmin = User::firstOrCreate([
            'email' => 'superadmin@gmail.com',
        ], [
            'name' => 'Super Administrator',
            'password' => bcrypt('@Iamsuperadmin'),
            'aktif' => true,
        ]);
        $superAdmin->assignRole($superAdminRole);

        // 6. Buat Akun Admin
        $admin = User::firstOrCreate([
            'email' => 'admin@gmail.com',
        ], [
            'name' => 'Admin',
            'password' => bcrypt('admin'),
            'aktif' => true,
        ]);
        $admin->assignRole($adminRole);

        // 7. Buat Akun Pegawai 1 - 4
        for ($i = 1; $i <= 4; $i++) {
            $pegawai = User::firstOrCreate([
                'email' => "pegawai{$i}@gmail.com",
            ], [
                'name' => "Pegawai {$i}",
                'password' => bcrypt('pegawai'),
                'aktif' => true,
            ]);
            $pegawai->assignRole($pegawaiRole);
        }
    }
}
