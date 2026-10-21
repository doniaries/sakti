<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StrukturOrganisasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan Bidang sudah ada
        $sekretariat = Bidang::firstOrCreate(
            ['slug' => 'sekretariat'],
            ['nama' => 'Sekretariat']
        );

        $bidangPemasaran = Bidang::firstOrCreate(
            ['slug' => 'bidang-pemasaran-pariwisata'],
            ['nama' => 'Bidang Pemasaran Pariwisata']
        );

        $bidangDestinasi = Bidang::firstOrCreate(
            ['slug' => 'pengembangan-destinasi-pariwisata'],
            ['nama' => 'Pengembangan Destinasi Pariwisata']
        );

        $bidangDispora = Bidang::firstOrCreate(
            ['slug' => 'pemuda-dan-olahraga'],
            ['nama' => 'Pemuda dan Olahraga']
        );

        // 2. Baca data dari pegawai_80.json untuk update jabatan_id
        $jsonPath = database_path('seeders/pegawai_80.json');
        if (file_exists($jsonPath)) {
            $dataRaw = json_decode(file_get_contents($jsonPath), true);
            foreach ($dataRaw as $row) {
                $nip = $row['nip'] ?? null;
                $jabatanRaw = $row['jabatan'] ?? null;

                if ($nip && $jabatanRaw) {
                    $jabatan = Jabatan::whereRaw('LOWER(nama) = ?', [strtolower($jabatanRaw)])->first();
                    if ($jabatan) {
                        User::where('nip', $nip)->update(['jabatan_id' => $jabatan->id]);
                    }
                }
            }
        }

        // 3. Tentukan Pimpinan Tertinggi & Para Kepala Bidang
        $kadis = User::where('name', 'LIKE', '%AFRINELDI%')->first();
        $sekretaris = User::where('name', 'LIKE', '%DESMAWATI%')->first();
        $kabidPemasaran = User::where('name', 'LIKE', '%DONI ARIES%')->first();
        $kabidDestinasi = User::where('name', 'LIKE', '%WILDA ARDES%')->first();
        $kabidDispora = User::where('name', 'LIKE', '%INDRAWADI%')->first();

        // 4. Update Hierarki Atasan & Kepala Bidang
        if ($kadis) {
            $kadis->update([
                'atasan_id' => null,
                'bidang_id' => null, // Pimpinan Tertinggi Seluruh Dinas
            ]);
        }

        $kadisId = $kadis?->id;

        if ($sekretaris) {
            $sekretaris->update([
                'atasan_id' => $kadisId,
                'bidang_id' => $sekretariat->id,
            ]);
            $sekretariat->update(['kepala_id' => $sekretaris->id]);
        }

        if ($kabidPemasaran) {
            $kabidPemasaran->update([
                'atasan_id' => $kadisId,
                'bidang_id' => $bidangPemasaran->id,
            ]);
            $bidangPemasaran->update(['kepala_id' => $kabidPemasaran->id]);
        }

        if ($kabidDestinasi) {
            $kabidDestinasi->update([
                'atasan_id' => $kadisId,
                'bidang_id' => $bidangDestinasi->id,
            ]);
            $bidangDestinasi->update(['kepala_id' => $kabidDestinasi->id]);
        }

        if ($kabidDispora) {
            $kabidDispora->update([
                'atasan_id' => $kadisId,
                'bidang_id' => $bidangDispora->id,
            ]);
            $bidangDispora->update(['kepala_id' => $kabidDispora->id]);
        }

        // 5. Distribusikan Staf Berdasarkan Jabatan & Kebutuhan Bidang
        $pimpinanIds = array_filter([
            $kadis?->id,
            $sekretaris?->id,
            $kabidPemasaran?->id,
            $kabidDestinasi?->id,
            $kabidDispora?->id,
        ]);

        $stafUsers = User::whereNotIn('id', $pimpinanIds)->with('jabatan')->get();

        foreach ($stafUsers as $staf) {
            $jabatanNama = strtolower($staf->jabatan?->nama ?? '');

            // Aturan penempatan berdasarkan nama jabatan / keahlian:
            if (
                str_contains($jabatanNama, 'pelatih') ||
                str_contains($jabatanNama, 'olahraga') ||
                str_contains($jabatanNama, 'pemuda')
            ) {
                // Bidang Pemuda dan Olahraga
                $staf->update([
                    'bidang_id' => $bidangDispora->id,
                    'atasan_id' => $kabidDispora?->id,
                ]);
            } elseif (
                str_contains($jabatanNama, 'pemasaran') ||
                str_contains($jabatanNama, 'ekonomi kreatif') ||
                str_contains($jabatanNama, 'adyatama') ||
                str_contains($jabatanNama, 'hubungan msyarakat') ||
                str_contains($jabatanNama, 'penerjemah')
            ) {
                // Bidang Pemasaran Pariwisata
                $staf->update([
                    'bidang_id' => $bidangPemasaran->id,
                    'atasan_id' => $kabidPemasaran?->id,
                ]);
            } elseif (
                str_contains($jabatanNama, 'destinasi') ||
                str_contains($jabatanNama, 'konservator') ||
                str_contains($jabatanNama, 'teknik')
            ) {
                // Bidang Pengembangan Destinasi Pariwisata
                $staf->update([
                    'bidang_id' => $bidangDestinasi->id,
                    'atasan_id' => $kabidDestinasi?->id,
                ]);
            } else {
                // Sekretariat (Kasubbag Umum, Perencana, Keuangan, TI, Pengadministrasi, dll)
                $staf->update([
                    'bidang_id' => $sekretariat->id,
                    'atasan_id' => $sekretaris?->id,
                ]);
            }
        }
    }
}
