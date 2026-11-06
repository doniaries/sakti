<?php

namespace Database\Seeders;

use App\Enums\GolonganRuang;
use App\Models\RiwayatGolongan;
use App\Models\RiwayatJabatan;
use App\Models\User;
use Illuminate\Database\Seeder;

class RiwayatKepangkatanDanJabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doni = User::where('email', 'doniaries@gmail.com')
            ->orWhere('name', 'like', '%DONI ARIES%')
            ->first();

        if ($doni) {
            // 1. Seed Riwayat Golongan / Pangkat Doni Aries
            RiwayatGolongan::where('user_id', $doni->id)->delete();

            $riwayatGolonganDoni = [
                [
                    'golongan' => GolonganRuang::IV_A,
                    'nama_pangkat' => 'Pembina',
                    'nomor_sk' => '823.4/1136/BKD-2026',
                    'tanggal_sk' => '2026-03-20',
                    'tmt_golongan' => '2026-04-01',
                    'is_golongan_terakhir' => true,
                ],
                [
                    'golongan' => GolonganRuang::III_D,
                    'nama_pangkat' => 'Penata Tingkat I',
                    'nomor_sk' => '823.3/210/BKPSDM-2022',
                    'tanggal_sk' => '2022-03-15',
                    'tmt_golongan' => '2022-04-01',
                    'is_golongan_terakhir' => false,
                ],
                [
                    'golongan' => GolonganRuang::III_C,
                    'nama_pangkat' => 'Penata',
                    'nomor_sk' => '823.3/12/BKPSDM-2018',
                    'tanggal_sk' => '2018-03-18',
                    'tmt_golongan' => '2018-04-01',
                    'is_golongan_terakhir' => false,
                ],
                [
                    'golongan' => GolonganRuang::III_B,
                    'nama_pangkat' => 'Penata Muda Tingkat I',
                    'nomor_sk' => '823.3/23/BKD-2016',
                    'tanggal_sk' => '2016-03-10',
                    'tmt_golongan' => '2016-04-01',
                    'is_golongan_terakhir' => false,
                ],
                [
                    'golongan' => GolonganRuang::III_A,
                    'nama_pangkat' => 'Penata Muda',
                    'nomor_sk' => '823.3/28/BKD-2012',
                    'tanggal_sk' => '2012-03-25',
                    'tmt_golongan' => '2012-04-01',
                    'is_golongan_terakhir' => false,
                ],
                [
                    'golongan' => GolonganRuang::II_C,
                    'nama_pangkat' => 'Pengatur',
                    'nomor_sk' => '823.2/35/BKD-2011',
                    'tanggal_sk' => '2011-03-12',
                    'tmt_golongan' => '2011-04-01',
                    'is_golongan_terakhir' => false,
                ],
                [
                    'golongan' => GolonganRuang::II_B,
                    'nama_pangkat' => 'Pengatur Muda Tingkat I',
                    'nomor_sk' => '832.40/BKD-2007',
                    'tanggal_sk' => '2007-03-14',
                    'tmt_golongan' => '2007-04-01',
                    'is_golongan_terakhir' => false,
                ],
                [
                    'golongan' => GolonganRuang::II_A,
                    'nama_pangkat' => 'Pengatur Muda',
                    'nomor_sk' => '813/08/BKD-2003',
                    'tanggal_sk' => '2002-11-20',
                    'tmt_golongan' => '2002-12-01',
                    'is_golongan_terakhir' => false,
                ],
            ];

            foreach ($riwayatGolonganDoni as $item) {
                $doni->riwayatGolongan()->create($item);
            }

            // 2. Seed Riwayat Jabatan Doni Aries
            RiwayatJabatan::where('user_id', $doni->id)->delete();

            $riwayatJabatanDoni = [
                [
                    'nama_jabatan' => 'Kepala BIDANG PEMASARAN PARIWISATA',
                    'jenis_jabatan' => 'Jabatan Struktural',
                    'unit_kerja' => 'Dinas Pariwisata, Pemuda dan Olahraga',
                    'nomor_sk' => '821.2/04/BKPSDM-2026',
                    'tanggal_sk' => '2026-02-03',
                    'tmt_jabatan' => '2026-02-03',
                    'is_jabatan_sekarang' => true,
                ],
                [
                    'nama_jabatan' => 'Pranata Komputer Ahli Muda',
                    'jenis_jabatan' => 'Jabatan Fungsional',
                    'unit_kerja' => 'Dinas Komunikasi dan Informatika',
                    'nomor_sk' => '821.29/188/BKPSDM-2021',
                    'tanggal_sk' => '2021-12-31',
                    'tmt_jabatan' => '2021-12-31',
                    'is_jabatan_sekarang' => false,
                ],
                [
                    'nama_jabatan' => 'KEPALA SEKSI INFRASTRUKTUR TEKNOLOGI INFORMATIKA',
                    'jenis_jabatan' => 'Jabatan Struktural',
                    'unit_kerja' => 'Dinas Komunikasi dan Informatika',
                    'nomor_sk' => '821.2/142/BKPSDM-2021',
                    'tanggal_sk' => '2021-09-24',
                    'tmt_jabatan' => '2021-09-24',
                    'is_jabatan_sekarang' => false,
                ],
                [
                    'nama_jabatan' => 'KEPALA SEKSI PERENCANAAN DAN PERIZINAN',
                    'jenis_jabatan' => 'Jabatan Struktural',
                    'unit_kerja' => 'Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu',
                    'nomor_sk' => '821.2/02/BKD-2020',
                    'tanggal_sk' => '2020-01-06',
                    'tmt_jabatan' => '2020-01-06',
                    'is_jabatan_sekarang' => false,
                ],
                [
                    'nama_jabatan' => 'KEPALA SEKSI PELAYANAN PERIZINAN UMUM',
                    'jenis_jabatan' => 'Jabatan Struktural',
                    'unit_kerja' => 'Badan Penanaman Modal dan Pelayanan Terpadu',
                    'nomor_sk' => '821.2/98/BKD-2016',
                    'tanggal_sk' => '2016-12-30',
                    'tmt_jabatan' => '2016-12-30',
                    'is_jabatan_sekarang' => false,
                ],
                [
                    'nama_jabatan' => 'FUNGSIONAL UMUM',
                    'jenis_jabatan' => 'Jabatan Pelaksana',
                    'unit_kerja' => 'Kantor Pelayanan Perizinan Terpadu',
                    'nomor_sk' => '820/12/BKD-2016',
                    'tanggal_sk' => '2016-04-01',
                    'tmt_jabatan' => '2016-04-01',
                    'is_jabatan_sekarang' => false,
                ],
                [
                    'nama_jabatan' => 'PNS TUGAS BELAJAR',
                    'jenis_jabatan' => 'Jabatan Pelaksana',
                    'unit_kerja' => 'Badan Kepegawaian Daerah',
                    'nomor_sk' => '892.1/55/BKD-2013',
                    'tanggal_sk' => '2013-07-31',
                    'tmt_jabatan' => '2013-07-31',
                    'is_jabatan_sekarang' => false,
                ],
            ];

            foreach ($riwayatJabatanDoni as $item) {
                $doni->riwayatJabatan()->create($item);
            }
        }

        // Migrasi data awal untuk pegawai lainnya yang sudah memiliki pangkat_golongan dan jabatan
        $users = User::when($doni, fn ($q) => $q->where('id', '!=', $doni->id))->get();

        foreach ($users as $user) {
            // Golongan awal
            if ($user->pangkat_golongan && ! $user->riwayatGolongan()->exists()) {
                $user->riwayatGolongan()->create([
                    'golongan' => $user->pangkat_golongan,
                    'nama_pangkat' => RiwayatGolongan::getPangkatByGolongan($user->pangkat_golongan),
                    'nomor_sk' => 'SK/PANGKAT/' . ($user->nip ?: $user->id),
                    'tanggal_sk' => $user->tmt_pangkat ?: now(),
                    'tmt_golongan' => $user->tmt_pangkat ?: now(),
                    'is_golongan_terakhir' => true,
                ]);
            }

            // Jabatan awal
            if ($user->jabatan_id && ! $user->riwayatJabatan()->exists()) {
                $namaJabatan = $user->jabatan?->nama ?? 'Pegawai';
                $jenisJabatan = (str_contains($namaJabatan, 'Kepala') || str_contains($namaJabatan, 'Kabid') || str_contains($namaJabatan, 'Sekretaris') || str_contains($namaJabatan, 'Kasubbag'))
                    ? 'Jabatan Struktural'
                    : 'Jabatan Fungsional';

                $user->riwayatJabatan()->create([
                    'nama_jabatan' => $namaJabatan,
                    'jenis_jabatan' => $jenisJabatan,
                    'unit_kerja' => $user->bidang?->nama ?? 'Dinas Pariwisata, Pemuda dan Olahraga',
                    'nomor_sk' => 'SK/JABATAN/' . ($user->nip ?: $user->id),
                    'tmt_jabatan' => now(),
                    'is_jabatan_sekarang' => true,
                ]);
            }
        }
    }
}
