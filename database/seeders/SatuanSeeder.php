<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatuanSeeder extends Seeder
{
    public function run(): void
    {
        $satuanTemplate = [
            ['nama' => 'Lusin'],
            ['nama' => 'Buah'],
            ['nama' => 'Unit'],
            ['nama' => 'Lembar'],
            ['nama' => 'Pack'],
            ['nama' => 'Box'],
            ['nama' => 'Rim'],
            ['nama' => 'Set'],
            ['nama' => 'Roll'],
            ['nama' => 'Botol'],
            ['nama' => 'Pcs'],
        ];

        foreach ($satuanTemplate as $satuan) {
            DB::table('satuans')->updateOrInsert(
                ['nama' => $satuan['nama']],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
