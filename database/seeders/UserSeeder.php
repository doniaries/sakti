<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 2. Buat Role
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);

        // 3. Beri Semua Permission ke Role Super Admin
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);

        // 3.5. Beri Permission ke Role Admin (Semua kecuali Role)
        $adminPermissions = $allPermissions->filter(function ($permission) {
            $name = strtolower($permission->name);
            return !str_contains($name, 'role') && !str_contains($name, 'permission');
        });
        $adminRole->syncPermissions($adminPermissions);

        // 4. Beri Permission ke Role Pegawai (Hanya Melihat Data Sendiri)
        $pegawaiPermissions = $allPermissions->filter(function ($permission) {
            // Pegawai hanya butuh izin "view" atau "view_any" pada menu User dan Absensi
            return in_array($permission->name, [
                'View:User',
                'ViewAny:User',
                'View:AbsensiHarian',
                'ViewAny:AbsensiHarian',
                'View:LogAbsensi',
                'ViewAny:LogAbsensi',
                'View:AbsensiMobile',
                'ViewAny:AbsensiMobile',
                'View:DashboardStatsWidget',
            ]);
        });
        $pegawaiRole->syncPermissions($pegawaiPermissions);

    }
}
