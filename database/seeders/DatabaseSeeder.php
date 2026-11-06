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
            JadwalKerjaSeeder::class,
            JenisIzinSeeder::class,
            MesinAbsensiSeeder::class,
            LogAbsensiSeeder::class,
            AktivitasSeeder::class,
            StrukturOrganisasiSeeder::class,
            SatuanSeeder::class,
            KodeRekeningSeeder::class,
            MasterBarangSeeder::class,
            KabupatenSijunjungSeeder::class,
            MasterTujuanBbmSeeder::class,
            MasterSyaratBelanjaSeeder::class,
            KegiatanSubKegiatanSeeder::class,
            RincianAnggaranBelanjaSeeder::class,
            PengaturanAplikasiSeeder::class,
            PengajuanSpjSeeder::class,
            ActivityLogSeeder::class,
            // SatuTahunStressTestSeeder::class, // Aktifkan seeder ini jika ingin menguji 1 tahun data (~20.000 data) di server online
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
