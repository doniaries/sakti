<?php

namespace Database\Seeders;

use App\Models\Bidang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BidangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bidangs = [
            'Sekretariat',
            'Bidang Pemasaran Pariwisata',
            'Pengembangan Destinasi Pariwisata',
            'Pemuda dan Olahraga',
        ];

        foreach ($bidangs as $bidang) {
            Bidang::updateOrCreate(
                ['slug' => Str::slug($bidang)],
                ['nama' => $bidang]
            );
        }
    }
}
