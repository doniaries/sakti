<?php

namespace Database\Seeders;

use App\Enums\JenjangPendidikan;
use App\Models\RiwayatPendidikan;
use App\Models\User;
use Illuminate\Database\Seeder;

class RiwayatPendidikanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Data Riwayat Pendidikan Resmi MyASN untuk Doni Aries
        $doni = User::where('email', 'doniaries@gmail.com')
            ->orWhere('name', 'like', '%DONI ARIES%')
            ->first();

        if ($doni) {
            RiwayatPendidikan::where('user_id', $doni->id)->delete();

            $riwayatDoni = [
                [
                    'jenjang' => JenjangPendidikan::S2,
                    'nama_institusi' => 'Kementerian Kominfo / CIO',
                    'jurusan' => 'CHIEF INFORMATION OFFICER',
                    'tahun_lulus' => 2015,
                    'nomor_ijazah' => 'S2/CIO/2015/001',
                    'is_pendidikan_terakhir' => true,
                ],
                [
                    'jenjang' => JenjangPendidikan::S1,
                    'nama_institusi' => 'UNIVERSITAS TERBUKA',
                    'jurusan' => 'ILMU PEMERINTAHAN',
                    'tahun_lulus' => 2011,
                    'nomor_ijazah' => 'S1/IP/2011/045',
                    'is_pendidikan_terakhir' => false,
                ],
                [
                    'jenjang' => JenjangPendidikan::SLTA,
                    'nama_institusi' => 'SMKN 1 SIJUNJUNG',
                    'jurusan' => 'SEKOLAH MENENGAH KEJURUAN',
                    'tahun_lulus' => 2000,
                    'nomor_ijazah' => 'SMK/2000/012',
                    'is_pendidikan_terakhir' => false,
                ],
                [
                    'jenjang' => JenjangPendidikan::SLTP,
                    'nama_institusi' => 'SMPN 7 SIJUNJUNG',
                    'jurusan' => 'SLTP UMUM',
                    'tahun_lulus' => 1997,
                    'nomor_ijazah' => 'SMP/1997/088',
                    'is_pendidikan_terakhir' => false,
                ],
                [
                    'jenjang' => JenjangPendidikan::SD,
                    'nama_institusi' => 'SDN No. 46 Muaro Gambok',
                    'jurusan' => 'SEKOLAH DASAR',
                    'tahun_lulus' => 1994,
                    'nomor_ijazah' => 'SD/1994/033',
                    'is_pendidikan_terakhir' => false,
                ],
            ];

            foreach ($riwayatDoni as $data) {
                $doni->riwayatPendidikan()->create($data);
            }

            $doni->update([
                'jenjang_pendidikan' => 'S2',
                'pendidikan' => 'S2 CHIEF INFORMATION OFFICER',
            ]);
        }

        // 2. Migrasi data awal untuk pegawai lainnya yang memiliki teks pendidikan
        $users = User::whereNotNull('pendidikan')
            ->when($doni, fn ($q) => $q->where('id', '!=', $doni->id))
            ->get();

        foreach ($users as $user) {
            if ($user->riwayatPendidikan()->exists()) {
                continue;
            }

            $text = trim((string) $user->pendidikan);
            $jenjang = JenjangPendidikan::S1; // Default
            $jurusan = $text;

            if (preg_match('/^S3\b/i', $text)) {
                $jenjang = JenjangPendidikan::S3;
            } elseif (preg_match('/^S2\b/i', $text)) {
                $jenjang = JenjangPendidikan::S2;
            } elseif (preg_match('/^S1\b/i', $text)) {
                $jenjang = JenjangPendidikan::S1;
            } elseif (preg_match('/^D4\b/i', $text)) {
                $jenjang = JenjangPendidikan::D4;
            } elseif (preg_match('/^D3\b/i', $text)) {
                $jenjang = JenjangPendidikan::D3;
            } elseif (preg_match('/^D2\b/i', $text)) {
                $jenjang = JenjangPendidikan::D2;
            } elseif (preg_match('/^D1\b/i', $text)) {
                $jenjang = JenjangPendidikan::D1;
            } elseif (preg_match('/^(SLTA|SMA|SMK|MA)\b/i', $text)) {
                $jenjang = JenjangPendidikan::SLTA;
            } elseif (preg_match('/^(SLTP|SMP|MTs)\b/i', $text)) {
                $jenjang = JenjangPendidikan::SLTP;
            } elseif (preg_match('/^SD\b/i', $text)) {
                $jenjang = JenjangPendidikan::SD;
            }

            $user->riwayatPendidikan()->create([
                'jenjang' => $jenjang,
                'nama_institusi' => 'Institusi Pendidikan Terdaftar',
                'jurusan' => $jurusan,
                'tahun_lulus' => null,
                'is_pendidikan_terakhir' => true,
            ]);

            $user->update([
                'jenjang_pendidikan' => $jenjang->value,
            ]);
        }
    }
}

