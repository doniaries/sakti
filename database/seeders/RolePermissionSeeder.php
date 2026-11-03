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
        // Pastikan role super_admin, admin, dan pegawai sudah ada
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);

        // Buat permissions widget jika belum ada agar seeder tidak error karena data kosong
        Permission::firstOrCreate(['name' => 'View:PegawaiAbsensiBulanChartWidget', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'View:PegawaiAbsensiBulanStatsWidget', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'View:KeuanganBidangOverviewWidget', 'guard_name' => 'web']);

        // === 0. Setup Role Super Admin ===
        $superAdminRole->syncPermissions(Permission::all());

        // === 1. Setup Role Admin Dinas ===
        // Admin bisa melakukan semuanya (termasuk ViewAny:Bidang & View:Bidang) kecuali mengatur Role
        $adminPermissions = Permission::where('name', 'not like', '%Role%')->get();
        $adminRole->syncPermissions($adminPermissions);

        // === 2. Setup Role Pegawai (Staf & Kepala Bidang) ===
        // Pegawai bisa mengelola Keuangan & SPJ pada bidangnya sendiri (TIDAK diberi ViewAny:Bidang / View:Bidang)
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
            // Permissions Modul Keuangan & SPJ (scoping per Bidang via Shield Policy)
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
            // Widgets
            'View:KeuanganBidangOverviewWidget',
            'View:LatestAbsensiWidget',
            'View:PegawaiAbsensiBulanChartWidget',
            'View:PegawaiAbsensiBulanStatsWidget',
        ])->get();

        $pegawaiRole->syncPermissions($pegawaiPermissions);
        
        $this->command->info('Hak Akses (Permissions) untuk Admin dan Pegawai telah berhasil disinkronisasi.');
    }
}
