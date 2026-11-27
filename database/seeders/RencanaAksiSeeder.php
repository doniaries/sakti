<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\Jabatan;
use App\Models\RencanaAksi;
use Illuminate\Database\Seeder;

class RencanaAksiSeeder extends Seeder
{
    public function run(): void
    {
        $bidangPariwisata = Bidang::where('nama', 'like', '%Pariwisata%')->first();
        if (! $bidangPariwisata) {
            return;
        }

        $tahun = (int) date('Y');
        $sasaranProgram = 'Program Peningkatan Daya Tarik Destinasi Pariwisata';

        $jabatanKabid = Jabatan::where('nama', 'like', '%Kepala Bidang%Pariwisata%')->first();
        $jabatanJF = Jabatan::where('nama', 'like', '%Analis%')->orWhere('nama', 'like', '%Fungsional%')->first();
        $jabatanStaf = Jabatan::where('nama', 'like', '%Penelaah%')->orWhere('nama', 'like', '%Pengelola%')->orWhere('nama', 'like', '%Staf%')->first();

        $kelompok = [
            [
                'jabatan_id' => $jabatanKabid?->id,
                'items' => [
                    ['nama_rencana_aksi' => 'Terlaksananya perencanaan kawasan strategis pariwisata kabupaten/kota', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Perencanaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Ditetapkannya kawasan strategis pariwisata kabupaten/kota', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Penetapan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Tersedia dan terpeliharanya sarana dan prasarana dalam pengelolaan kawasan strategis pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya penerapan destinasi pariwisata berkelanjutan dalam pengelolaan kawasan strategis pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Penerapan Destinasi Pariwisata Berkelanjutan dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya monitoring dan evaluasi pengelolaan kawasan strategis pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya peningkatan kapasitas SDM pengelola kawasan strategis pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Peningkatan Kapasitas SDM Pengelola Kawasan Strategis Pariwisata'],
                    ['nama_rencana_aksi' => 'Terlaksananya perencanaan destinasi pariwisata kabupaten/kota', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Perencanaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya pengembangan destinasi pariwisata sesuai tahapan (rintisan, berkembang, pemantapan, revitalisasi)', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Pengembangan Destinasi Pariwisata Kabupaten/Kota Sesuai dengan Tahapan (Rintisan, Berkembang, Pemantapan, Revitalisasi)'],
                    ['nama_rencana_aksi' => 'Tersedia dan terpeliharanya sarana dan prasarana dalam pengelolaan destinasi pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya pemberdayaan masyarakat dalam pengelolaan destinasi pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Pemberdayaan Masyarakat dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya monitoring dan evaluasi pengelolaan destinasi pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya koordinasi dan pembinaan internal bidang pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Umum'],
                    ['nama_rencana_aksi' => 'Terlaksananya tugas direktif/penugasan khusus dari pimpinan sesuai arahan dan ketentuan yang berlaku', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Umum'],
                ],
            ],
            [
                'jabatan_id' => $jabatanJF?->id,
                'items' => [
                    ['nama_rencana_aksi' => 'Tersusunnya dokumen perencanaan kawasan strategis pariwisata', 'jenis' => 'trajectory', 'satuan' => 'dokumen', 'sub_kegiatan' => 'Perencanaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya survei dan pemetaan potensi kawasan strategis pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Perencanaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Tersusunnya dokumen penetapan kawasan strategis pariwisata', 'jenis' => 'trajectory', 'satuan' => 'dokumen', 'sub_kegiatan' => 'Penetapan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya verifikasi lapangan calon kawasan strategis pariwisata', 'jenis' => 'trajectory', 'satuan' => 'lokasi', 'sub_kegiatan' => 'Penetapan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya inventarisasi (opname) sarana dan prasarana kawasan strategis pariwisata', 'jenis' => 'trajectory', 'satuan' => 'lokasi', 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya pengawasan dan pemeriksaan hasil pekerjaan sarana prasarana kawasan strategis', 'jenis' => 'trajectory', 'satuan' => 'paket', 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Tersusunnya laporan hasil pekerjaan sarana dan prasarana kawasan strategis', 'jenis' => 'trajectory', 'satuan' => 'laporan', 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya pendampingan penerapan destinasi pariwisata berkelanjutan', 'jenis' => 'trajectory', 'satuan' => 'lokasi', 'sub_kegiatan' => 'Penerapan Destinasi Pariwisata Berkelanjutan dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya penilaian penerapan kriteria destinasi pariwisata berkelanjutan', 'jenis' => 'trajectory', 'satuan' => 'lokasi', 'sub_kegiatan' => 'Penerapan Destinasi Pariwisata Berkelanjutan dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Tersusunnya laporan hasil monitoring dan evaluasi pengelolaan kawasan strategis pariwisata', 'jenis' => 'trajectory', 'satuan' => 'laporan', 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya kunjungan monitoring kawasan strategis pariwisata', 'jenis' => 'trajectory', 'satuan' => 'kunjungan', 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya pelatihan/bimbingan teknis pengelola kawasan strategis pariwisata', 'jenis' => 'trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Peningkatan Kapasitas SDM Pengelola Kawasan Strategis Pariwisata'],
                    ['nama_rencana_aksi' => 'Terlaksananya pendampingan pasca pelatihan pengelola kawasan strategis pariwisata', 'jenis' => 'trajectory', 'satuan' => 'kunjungan', 'sub_kegiatan' => 'Peningkatan Kapasitas SDM Pengelola Kawasan Strategis Pariwisata'],
                    ['nama_rencana_aksi' => 'Tersusunnya dokumen perencanaan destinasi pariwisata', 'jenis' => 'trajectory', 'satuan' => 'dokumen', 'sub_kegiatan' => 'Perencanaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya survei dan pemetaan potensi destinasi pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Perencanaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya penilaian tahapan pengembangan destinasi pariwisata', 'jenis' => 'trajectory', 'satuan' => 'destinasi', 'sub_kegiatan' => 'Pengembangan Destinasi Pariwisata Kabupaten/Kota Sesuai dengan Tahapan (Rintisan, Berkembang, Pemantapan, Revitalisasi)'],
                    ['nama_rencana_aksi' => 'Terlaksananya pendampingan pengembangan destinasi binaan', 'jenis' => 'trajectory', 'satuan' => 'kunjungan', 'sub_kegiatan' => 'Pengembangan Destinasi Pariwisata Kabupaten/Kota Sesuai dengan Tahapan (Rintisan, Berkembang, Pemantapan, Revitalisasi)'],
                    ['nama_rencana_aksi' => 'Terlaksananya fasilitasi pemenuhan kriteria One Village One Destination pada destinasi binaan', 'jenis' => 'trajectory', 'satuan' => 'destinasi', 'sub_kegiatan' => 'Pengembangan Destinasi Pariwisata Kabupaten/Kota Sesuai dengan Tahapan (Rintisan, Berkembang, Pemantapan, Revitalisasi)'],
                    ['nama_rencana_aksi' => 'Terlaksananya inventarisasi (opname) sarana dan prasarana destinasi pariwisata', 'jenis' => 'trajectory', 'satuan' => 'lokasi', 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya pengawasan dan pemeriksaan hasil pekerjaan sarana prasarana destinasi', 'jenis' => 'trajectory', 'satuan' => 'paket', 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Tersusunnya laporan hasil pekerjaan sarana dan prasarana destinasi', 'jenis' => 'trajectory', 'satuan' => 'laporan', 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya pelatihan dan pembinaan Pokdarwis/kelompok masyarakat pengelola destinasi', 'jenis' => 'trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Pemberdayaan Masyarakat dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya pendampingan kelompok masyarakat dalam pengelolaan destinasi', 'jenis' => 'trajectory', 'satuan' => 'kunjungan', 'sub_kegiatan' => 'Pemberdayaan Masyarakat dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Tersusunnya laporan hasil pemberdayaan masyarakat dalam pengelolaan destinasi', 'jenis' => 'trajectory', 'satuan' => 'laporan', 'sub_kegiatan' => 'Pemberdayaan Masyarakat dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Tersusunnya laporan hasil monitoring dan evaluasi pengelolaan destinasi pariwisata', 'jenis' => 'trajectory', 'satuan' => 'laporan', 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya kunjungan monitoring destinasi pariwisata', 'jenis' => 'trajectory', 'satuan' => 'kunjungan', 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya koordinasi teknis dan penyusunan bahan pimpinan bidang pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Umum'],
                    ['nama_rencana_aksi' => 'Terlaksananya tugas direktif/penugasan khusus dari pimpinan sesuai arahan dan ketentuan yang berlaku', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Umum'],
                ],
            ],
            [
                'jabatan_id' => $jabatanStaf?->id,
                'items' => [
                    ['nama_rencana_aksi' => 'Terkumpulnya data dan bahan perencanaan kawasan strategis pariwisata', 'jenis' => 'trajectory', 'satuan' => 'data', 'sub_kegiatan' => 'Perencanaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya dukungan administrasi perencanaan kawasan strategis pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Perencanaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terdistribusikannya dokumen penetapan kawasan strategis pariwisata kepada pihak terkait', 'jenis' => 'trajectory', 'satuan' => 'dokumen', 'sub_kegiatan' => 'Penetapan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya dukungan administrasi penetapan kawasan strategis pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Penetapan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terdokumentasikannya kondisi sarana prasarana kawasan strategis (foto sebelum, progres, sesudah)', 'jenis' => 'trajectory', 'satuan' => 'lokasi', 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya dukungan administrasi pekerjaan sarana prasarana kawasan strategis', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terinputnya data penerapan destinasi pariwisata berkelanjutan', 'jenis' => 'trajectory', 'satuan' => 'data', 'sub_kegiatan' => 'Penerapan Destinasi Pariwisata Berkelanjutan dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya dukungan administrasi penerapan destinasi pariwisata berkelanjutan', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Penerapan Destinasi Pariwisata Berkelanjutan dalam Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terekapnya data hasil monitoring kawasan strategis pariwisata', 'jenis' => 'trajectory', 'satuan' => 'data', 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya dukungan administrasi monitoring dan evaluasi kawasan strategis', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Kawasan Strategis Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terdatanya peserta pelatihan pengelola kawasan strategis pariwisata', 'jenis' => 'trajectory', 'satuan' => 'data', 'sub_kegiatan' => 'Peningkatan Kapasitas SDM Pengelola Kawasan Strategis Pariwisata'],
                    ['nama_rencana_aksi' => 'Terlaksananya dukungan administrasi pelatihan pengelola kawasan strategis', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Peningkatan Kapasitas SDM Pengelola Kawasan Strategis Pariwisata'],
                    ['nama_rencana_aksi' => 'Terkumpulnya data dan bahan perencanaan destinasi pariwisata', 'jenis' => 'trajectory', 'satuan' => 'data', 'sub_kegiatan' => 'Perencanaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya dukungan administrasi perencanaan destinasi pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Perencanaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terinputnya data dan status tahapan destinasi pada SI DEWI MANDE', 'jenis' => 'trajectory', 'satuan' => 'data', 'sub_kegiatan' => 'Pengembangan Destinasi Pariwisata Kabupaten/Kota Sesuai dengan Tahapan (Rintisan, Berkembang, Pemantapan, Revitalisasi)'],
                    ['nama_rencana_aksi' => 'Terlaksananya dukungan administrasi pengembangan destinasi pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Pengembangan Destinasi Pariwisata Kabupaten/Kota Sesuai dengan Tahapan (Rintisan, Berkembang, Pemantapan, Revitalisasi)'],
                    ['nama_rencana_aksi' => 'Terdokumentasikannya kondisi sarana prasarana destinasi (foto sebelum, progres, sesudah)', 'jenis' => 'trajectory', 'satuan' => 'lokasi', 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya dukungan administrasi pekerjaan sarana prasarana destinasi', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Pengadaan/Pemeliharaan/Rehabilitasi Sarana dan Prasarana dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terdatanya kelompok masyarakat/Pokdarwis di destinasi pariwisata', 'jenis' => 'trajectory', 'satuan' => 'data', 'sub_kegiatan' => 'Pemberdayaan Masyarakat dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya dukungan administrasi pemberdayaan masyarakat dalam pengelolaan destinasi', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Pemberdayaan Masyarakat dalam Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terekapnya data hasil monitoring destinasi pariwisata', 'jenis' => 'trajectory', 'satuan' => 'data', 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya dukungan administrasi monitoring dan evaluasi destinasi', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Monitoring dan Evaluasi Pengelolaan Destinasi Pariwisata Kabupaten/Kota'],
                    ['nama_rencana_aksi' => 'Terlaksananya pelayanan administrasi dan pengelolaan arsip bidang pariwisata', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Umum'],
                    ['nama_rencana_aksi' => 'Terlaksananya tugas direktif/penugasan khusus dari pimpinan sesuai arahan dan ketentuan yang berlaku', 'jenis' => 'non_trajectory', 'satuan' => 'kegiatan', 'sub_kegiatan' => 'Umum'],
                ],
            ],
        ];

        foreach ($kelompok as $grup) {
            foreach ($grup['items'] as $item) {
                RencanaAksi::updateOrCreate(
                    [
                        'bidang_id' => $bidangPariwisata->id,
                        'jabatan_id' => $grup['jabatan_id'],
                        'tahun' => $tahun,
                        'rencana_aksi' => $item['nama_rencana_aksi'],
                    ],
                    [
                        'sasaran_program' => $sasaranProgram,
                        'sasaran_sub_kegiatan' => $item['sub_kegiatan'],
                        'indikator' => ucfirst($item['jenis']) . ' (' . $item['satuan'] . ')',
                    ]
                );
            }
        }
    }
}
