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
        $legacyMap = [
            'pemasaran-pariwisata' => 'Pariwisata',
            'pengembangan-destinasi-pariwisata' => 'Ekonomi Kreatif',
        ];

        foreach ($legacyMap as $oldSlug => $newName) {
            $newSlug = Str::slug($newName);
            $oldBidang = Bidang::query()->where('slug', $oldSlug)->first();
            $newBidangExists = Bidang::query()->where('slug', $newSlug)->exists();

            if ($oldBidang && ! $newBidangExists) {
                $oldBidang->update([
                    'nama' => $newName,
                    'slug' => $newSlug,
                ]);
            }
        }

        $bidangs = [
            'Sekretariat',
            'Pariwisata',
            'Ekonomi Kreatif',
            'Pemuda dan Olahraga',
            'Badan Pengelola Geopark',
        ];

        foreach ($bidangs as $bidang) {
            Bidang::updateOrCreate(
                ['slug' => Str::slug($bidang)],
                ['nama' => $bidang]
            );
        }
    }
}
