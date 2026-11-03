<?php

namespace Database\Seeders;

use App\Enums\NamaBank;
use App\Models\MasterToko;
use Illuminate\Database\Seeder;

class MasterBankTokoSeeder extends Seeder
{
    public function run(): void
    {
        MasterToko::updateOrCreate(
            ['nama_toko' => 'Toko Rilex Abadi'],
            [
                'nama_pemilik' => 'Agusrizal',
                'telepon' => '0812-6789-1234',
                'alamat' => 'Muaro Sijunjung',
                'nama_bank' => NamaBank::BANK_NAGARI->value,
                'nomor_rekening' => '0700.0210.21699-2',
                'is_active' => true,
            ]
        );

        MasterToko::updateOrCreate(
            ['nama_toko' => 'Toko Sinar Jaya ATK'],
            [
                'nama_pemilik' => 'Hendra Putra',
                'telepon' => '0813-7411-2233',
                'alamat' => 'Pasar Inpres Muaro Sijunjung',
                'nama_bank' => NamaBank::BANK_NAGARI->value,
                'nomor_rekening' => '0700.0210.33412-1',
                'is_active' => true,
            ]
        );
    }
}
