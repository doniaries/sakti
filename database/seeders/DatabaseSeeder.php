<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Panggil ShieldSeeder jika ada (digenerate oleh Filament Shield)
        // Kita gunakan try-catch atau periksa apakah class ada, karena user mungkin belum run shield:generate
        if (class_exists('Database\Seeders\ShieldSeeder')) {
            $this->call('Database\Seeders\ShieldSeeder');
        }

        // 2. Buat Role (jaga-jaga jika ShieldSeeder belum dijalankan)
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);

        // 3. Buat Akun Super Admin
        $superAdmin = User::firstOrCreate([
            'email' => 'superadmin@gmail.com',
        ], [
            'name' => 'Super Administrator',
            'password' => bcrypt('@Iamsuperadmin'),
            'aktif' => true,
        ]);
        $superAdmin->assignRole($superAdminRole);

        // 4. Buat Akun Pegawai 1 - 4
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
