<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Seluruh Role Baku Sistem & Keuangan
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $paRole = Role::firstOrCreate(['name' => 'pa', 'guard_name' => 'web']);
        $ppkRole = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $pptkRole = Role::firstOrCreate(['name' => 'pptk', 'guard_name' => 'web']);
        $jfRole = Role::firstOrCreate(['name' => 'jf', 'guard_name' => 'web']);
        $bendaharaRole = Role::firstOrCreate(['name' => 'bendahara', 'guard_name' => 'web']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);

        // Hapus role usang yang tidak lagi dipakai
        Role::query()->whereIn('name', ['staf_keuangan', 'jf_staf'])->delete();

        // 2. Beri Semua Permission ke Role Super Admin
        $allPermissions = Permission::all();
        if ($allPermissions->isNotEmpty()) {
            $superAdminRole->syncPermissions($allPermissions);

            // 3. Beri Permission ke Role Admin (Semua kecuali Role)
            $adminPermissions = $allPermissions->filter(function ($permission) {
                $name = strtolower($permission->name);
                return !str_contains($name, 'role') && !str_contains($name, 'permission');
            });
            $adminRole->syncPermissions($adminPermissions);
        }
    }
}
