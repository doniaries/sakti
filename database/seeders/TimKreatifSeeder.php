<?php

namespace Database\Seeders;

use App\Enums\JabatanTimKreatif;
use App\Models\User;
use Illuminate\Database\Seeder;

class TimKreatifSeeder extends Seeder
{
    /**
     * Memetakan keanggotaan Tim Kreatif Publikasi dan Promosi
     * berdasarkan SK Kepala Dinas Nomor 188.45/28/KPTS-DISPARPORA/2026.
     */
    public function run(): void
    {
        $anggota = [
            // I. Pembina, Pengarah, dan Penanggung Jawab
            ['nama' => 'AFRINELDI', 'jabatan' => JabatanTimKreatif::PEMBINA],
            ['nama' => 'DESMAWATI', 'jabatan' => JabatanTimKreatif::PENGARAH],
            ['nama' => 'DONI ARIES', 'jabatan' => JabatanTimKreatif::PENANGGUNG_JAWAB_BIDANG],
            ['nama' => 'INDRAWADI R', 'jabatan' => JabatanTimKreatif::PENANGGUNG_JAWAB_BIDANG],
            ['nama' => 'WILDA ARDES N', 'jabatan' => JabatanTimKreatif::PENANGGUNG_JAWAB_BIDANG],

            // II. Koordinator Teknis
            ['nama' => 'NOFRIZAL M', 'jabatan' => JabatanTimKreatif::KOORDINATOR_TIM_KREATIF],
            ['nama' => 'WIRA GUSTI MUSTIKA', 'jabatan' => JabatanTimKreatif::KOORDINATOR_TEKNIS],
            ['nama' => 'INDRA JUNAIDI', 'jabatan' => JabatanTimKreatif::KOORDINATOR_TEKNIS],
            ['nama' => 'DIKI ZULFIKAR', 'jabatan' => JabatanTimKreatif::KOORDINATOR_TEKNIS],
            ['nama' => 'LIDEFRIYON', 'jabatan' => JabatanTimKreatif::KOORDINATOR_TEKNIS],
            ['nama' => 'HENDRA PERMANA', 'jabatan' => JabatanTimKreatif::KOORDINATOR_TEKNIS],
            ['nama' => 'MIRA APRIYANTI', 'jabatan' => JabatanTimKreatif::KOORDINATOR_TEKNIS],

            // III. Koordinator dan Pelaksana Teknis Website
            ['nama' => 'AGUSMEDI ARIF', 'jabatan' => JabatanTimKreatif::KOORDINATOR_WEBSITE],
            ['nama' => 'JENI ZULMARLY KAMIL', 'jabatan' => JabatanTimKreatif::PELAKSANA_WEBSITE],
            ['nama' => 'ANDRA PEBTALIYUS', 'jabatan' => JabatanTimKreatif::PELAKSANA_WEBSITE],
            ['nama' => 'NAZHIRUL RIFQI ALNEV', 'jabatan' => JabatanTimKreatif::PELAKSANA_WEBSITE],
            ['nama' => 'PRADIUTAMA JULIYANSA', 'jabatan' => JabatanTimKreatif::PELAKSANA_WEBSITE],
            ['nama' => 'FARHANUL HUDA', 'jabatan' => JabatanTimKreatif::PELAKSANA_WEBSITE],

            // IV. Koordinator dan Pelaksana Teknis Dokumentasi dan Desain Grafis
            ['nama' => 'DIO TRI ANANDA PUTRA', 'jabatan' => JabatanTimKreatif::KOORDINATOR_DESAIN_GRAFIS],
            ['nama' => 'MAULANA IBRAHIM', 'jabatan' => JabatanTimKreatif::KOORDINATOR_DOKUMENTASI],
            ['nama' => 'NONI WILDA SARI', 'jabatan' => JabatanTimKreatif::KOORDINATOR_MEDIA_SOSIAL],
            ['nama' => 'ROSY WITRIN', 'jabatan' => JabatanTimKreatif::PENGELOLA_MEDIA_SOSIAL],
            ['nama' => 'FITRI AMELIA', 'jabatan' => JabatanTimKreatif::PENERJEMAH],
            ['nama' => 'SYIFA SALSABILA', 'jabatan' => JabatanTimKreatif::DOKUMENTASI],
            ['nama' => 'RAGANDHI TANLIN', 'jabatan' => JabatanTimKreatif::DOKUMENTASI],

            // V. Administrasi
            ['nama' => 'DINDA PUTRI WENY', 'jabatan' => JabatanTimKreatif::ADMINISTRASI],
            ['nama' => 'SIZIAH JUANDRI', 'jabatan' => JabatanTimKreatif::ADMINISTRASI],

            // VI. Pengelola Geopark
            ['nama' => 'AFRINAL', 'jabatan' => JabatanTimKreatif::PENGELOLA_GEOPARK],
            ['nama' => 'MELGA SARI', 'jabatan' => JabatanTimKreatif::PENGELOLA_GEOPARK],
            ['nama' => 'SALSABILA MUKHSIS', 'jabatan' => JabatanTimKreatif::PENGELOLA_GEOPARK],
            ['nama' => 'RAHMAD', 'jabatan' => JabatanTimKreatif::PENGELOLA_GEOPARK],
        ];

        User::query()->whereNotNull('jabatan_tim_kreatif')->update(['jabatan_tim_kreatif' => null]);

        $users = User::query()->bukanSuperAdmin()->get(['id', 'name']);

        $matched = 0;
        $unmatched = [];

        foreach ($anggota as $item) {
            $needle = $this->normalize($item['nama']);

            $user = $users->first(function (User $u) use ($needle): bool {
                $baseName = strstr($u->name, ',', true) ?: $u->name;
                $normalized = $this->normalize($baseName);

                return $normalized === $needle;
            });

            if ($user) {
                $user->update(['jabatan_tim_kreatif' => $item['jabatan']]);
                $matched++;
            } else {
                $unmatched[] = $item['nama'];
            }
        }

        if ($this->command) {
            $this->command->info("TimKreatifSeeder: {$matched} pegawai berhasil diperbarui.");
            if ($unmatched !== []) {
                $this->command->warn('Belum ditemukan di tabel users: ' . implode(', ', $unmatched));
            }
        }
    }

    private function normalize(string $text): string
    {
        return (string) preg_replace('/[^a-z]/', '', strtolower($text));
    }
}
