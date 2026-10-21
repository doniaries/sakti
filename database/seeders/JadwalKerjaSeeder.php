<?php

namespace Database\Seeders;

use App\Models\JadwalKerja;
use Illuminate\Database\Seeder;

class JadwalKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'nama' => 'Jadwal Reguler',
                'kode' => 'reguler',
                'jam_masuk_senin_kamis' => '07:30:00',
                'jam_pulang_senin_kamis' => '16:00:00',
                'jam_masuk_jumat' => '07:30:00',
                'jam_pulang_jumat' => '16:30:00',
                'toleransi_terlambat_menit' => 15,
                'is_default' => true,
                'is_aktif' => true,
                'keterangan' => 'Jam kerja normal harian instansi. Senin-Kamis 07.30 - 16.00 WIB, Jumat 07.30 - 16.30 WIB.',
            ],
            [
                'nama' => 'Jadwal Bulan Puasa (Ramadhan)',
                'kode' => 'ramadhan',
                'jam_masuk_senin_kamis' => '08:00:00',
                'jam_pulang_senin_kamis' => '15:00:00',
                'jam_masuk_jumat' => '08:00:00',
                'jam_pulang_jumat' => '15:30:00',
                'toleransi_terlambat_menit' => 15,
                'is_default' => false,
                'is_aktif' => true,
                'keterangan' => 'Jam kerja khusus selama bulan suci Ramadhan. Senin-Kamis 08.00 - 15.00 WIB, Jumat 08.00 - 15.30 WIB.',
            ],
        ];

        foreach ($shifts as $shift) {
            JadwalKerja::updateOrCreate(
                ['kode' => $shift['kode']],
                $shift
            );
        }

        // Terapkan Jadwal Reguler sebagai default ke seluruh pegawai yang belum memiliki jadwal khusus
        $jadwalReguler = JadwalKerja::where('kode', 'reguler')->first();
        if ($jadwalReguler) {
            \App\Models\User::whereNull('jadwal_kerja_id')->update([
                'jadwal_kerja_id' => $jadwalReguler->id,
            ]);
        }
    }
}
