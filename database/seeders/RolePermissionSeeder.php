<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role Admin dan Pegawai sudah ada
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'Pegawai', 'guard_name' => 'web']);

        // Buat permissions widget jika belum ada agar seeder tidak error karena data kosong
        Permission::firstOrCreate(['name' => 'View:PegawaiAbsensiBulanChartWidget', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'View:PegawaiAbsensiBulanStatsWidget', 'guard_name' => 'web']);

        // === 1. Setup Role Admin ===
        // Admin bisa melakukan semuanya kecuali mengatur Role (Management Role/Permissions)
        $adminPermissions = Permission::where('name', 'not like', '%Role%')->get();
        $adminRole->syncPermissions($adminPermissions);

        // === 2. Setup Role Pegawai ===
        // Pegawai hanya bisa melihat log dan dashboard, serta mengedit profilnya sendiri
        $pegawaiPermissions = Permission::whereIn('name', [
            'ViewAny:AbsensiHarian',
            'View:AbsensiHarian',
            'ViewAny:LogAbsensi',
            'View:LogAbsensi',
            'ViewAny:Lhk',
            'View:Lhk',
            'Create:Lhk',
            'Update:Lhk',
            'Delete:Lhk',
            'View:User',
            'Update:User',
            // Allow them to see these widgets:
            'View:LatestAbsensiWidget',
            'View:PegawaiAbsensiBulanChartWidget',
            'View:PegawaiAbsensiBulanStatsWidget'
        ])->get();
        
        $pegawaiRole->syncPermissions($pegawaiPermissions);
        
        $this->command->info('Hak Akses (Permissions) untuk Admin dan Pegawai telah berhasil disinkronisasi.');
    }
}
