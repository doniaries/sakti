<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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

        $this->call([
            UserSeeder::class,
            InstansiSeeder::class,
            DataPegawaiSeeder::class,
            BidangSeeder::class,
            JabatanSeeder::class,
            MesinAbsensiSeeder::class,
        ]);

        // 3. Generate ulang permission secara dinamis berdasarkan model & panel terkini
        \Illuminate\Support\Facades\Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--no-interaction' => true,
        ]);
        
        // 4. Set otomatis permissions untuk Admin dan Pegawai
        $this->call(RolePermissionSeeder::class);
        
        $this->command->info('Shield permissions generated successfully.');
    }
}
