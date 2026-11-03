<?php

namespace Database\Seeders;

use App\Models\MasterBank;
use App\Models\MasterToko;
use Illuminate\Database\Seeder;

class MasterBankTokoSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            ['kode_bank' => '118', 'nama_bank' => 'Bank Nagari (BPD Sumbar)'],
            ['kode_bank' => '002', 'nama_bank' => 'Bank Rakyat Indonesia (BRI)'],
            ['kode_bank' => '009', 'nama_bank' => 'Bank Negara Indonesia (BNI)'],
            ['kode_bank' => '008', 'nama_bank' => 'Bank Mandiri'],
            ['kode_bank' => '451', 'nama_bank' => 'Bank Syariah Indonesia (BSI)'],
            ['kode_bank' => '014', 'nama_bank' => 'Bank Central Asia (BCA)'],
        ];

        $bankNagari = null;
        foreach ($banks as $bankData) {
            $bank = MasterBank::updateOrCreate(
                ['nama_bank' => $bankData['nama_bank']],
                ['kode_bank' => $bankData['kode_bank'], 'is_active' => true]
            );
            if ($bankData['kode_bank'] === '118') {
                $bankNagari = $bank;
            }
        }

        if ($bankNagari) {
            MasterToko::updateOrCreate(
                ['nama_toko' => 'Toko Rilex Abadi'],
                [
                    'nama_pemilik' => 'Agusrizal',
                    'telepon' => '0812-6789-1234',
                    'alamat' => 'Muaro Sijunjung',
                    'master_bank_id' => $bankNagari->id,
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
                    'master_bank_id' => $bankNagari->id,
                    'nomor_rekening' => '0700.0210.33412-1',
                    'is_active' => true,
                ]
            );
        }
    }
}
