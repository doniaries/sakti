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

        // 4. Beri Permission ke Role Pegawai (Data Sendiri & Keuangan Bidang Sendiri via Shield Policy)
        $pegawaiPermissions = $allPermissions->filter(function ($permission) {
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
                'View:KeuanganBidangOverviewWidget',
                'ViewAny:PengajuanSpj',
                'View:PengajuanSpj',
                'Create:PengajuanSpj',
                'Update:PengajuanSpj',
                'Delete:PengajuanSpj',
                'ViewAny:MasterKegiatan',
                'View:MasterKegiatan',
                'Create:MasterKegiatan',
                'Update:MasterKegiatan',
                'ViewAny:MasterSubKegiatan',
                'View:MasterSubKegiatan',
                'Create:MasterSubKegiatan',
                'Update:MasterSubKegiatan',
                'ViewAny:MasterToko',
                'View:MasterToko',
                'Create:MasterToko',
                'Update:MasterToko',
                'ViewAny:KodeRekening',
                'View:KodeRekening',
                'ViewAny:MasterBarang',
                'View:MasterBarang',
            ]);
        });
        $pegawaiRole->syncPermissions($pegawaiPermissions);

    }
}
