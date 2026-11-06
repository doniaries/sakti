<?php

namespace Database\Seeders;

use App\Enums\NamaBank;
use App\Models\Bidang;
use App\Models\DetailPengajuanSpj;
use App\Models\KodeRekening;
use App\Models\MasterBarang;
use App\Models\MasterKegiatan;
use App\Models\MasterSubKegiatan;
use App\Models\MasterToko;
use App\Models\PengajuanSpj;
use App\Models\RiwayatStatusSpj;
use App\Models\User;
use App\Models\VerifikasiSpj;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengajuanSpjSeeder extends Seeder
{
    /**
     * Seeder untuk membuat 2 pengajuan SPJ per bidang dengan berbagai status workflow
     * sehingga pengguna dapat mensimulasikan seluruh tahapan (Surat Pesanan, Realisasi, Verifikasi Berjenjang, Pencairan).
     */
    public function run(): void
    {
        // 1. Pastikan Master Toko Tersedia
        $tokoRilex = MasterToko::updateOrCreate(
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

        $tokoSinarJaya = MasterToko::updateOrCreate(
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

        $tokoKomputer = MasterToko::updateOrCreate(
            ['nama_toko' => 'CV. Mitra Komputer Sijunjung'],
            [
                'nama_pemilik' => 'Rahmat Hidayat',
                'telepon' => '0821-6988-7744',
                'alamat' => 'Jl. Jenderal Sudirman No. 15, Muaro Sijunjung',
                'nama_bank' => NamaBank::BANK_NAGARI->value,
                'nomor_rekening' => '0700.0210.88765-4',
                'is_active' => true,
            ]
        );

        // 2. Identifikasi Pejabat Resmi Dinas
        $paUser = User::where('name', 'like', '%AFRINELDI%')->first()
            ?? User::whereNull('bidang_id')->first()
            ?? User::first();

        $ppkUser = User::where('name', 'like', '%NOFRIZAL%')->first()
            ?? User::where('name', 'like', '%KASUBAG%')->first()
            ?? User::skip(1)->first();

        $bendaharaUser = User::where('name', 'like', '%WIWIRNA%')->first()
            ?? User::where('name', 'like', '%BENDAHARA%')->first()
            ?? User::skip(2)->first();

        // 3. Ambil Master Barang & Rekening
        $barangList = MasterBarang::with('satuan')->get();
        $rekeningList = KodeRekening::all();

        if ($barangList->isEmpty() || $rekeningList->isEmpty()) {
            $this->command->error('Data master barang atau kode rekening belum ada. Jalankan MasterBarangSeeder dan KodeRekeningSeeder terlebih dahulu.');
            return;
        }

        // 4. Bersihkan data SPJ lama agar simulasi segar
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DetailPengajuanSpj::truncate();
        RiwayatStatusSpj::truncate();
        VerifikasiSpj::truncate();
        PengajuanSpj::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $bidangs = Bidang::with('users')->get();
        $counter = 1;
        $now = Carbon::now();

        // Skenario simulasi per bidang (2 SPJ per bidang)
        $skenarioPerBidang = [
            // Bidang 1: Sekretariat
            1 => [
                [
                    'status' => 'diajukan',
                    'judul' => 'Pengadaan Bahan Logistik Kantor & ATK Triwulan IV',
                    'toko' => $tokoSinarJaya,
                    'items' => [
                        ['nama' => 'Pena Signo Uniball', 'qty' => 5, 'harga' => 23000],
                        ['nama' => 'Kertas HVS A4 80 gram', 'qty' => 10, 'harga' => 55000],
                    ],
                    'tgl' => $now->copy()->subDays(2),
                    'selisih' => false,
                ],
                [
                    'status' => 'dicairkan',
                    'judul' => 'Penyediaan Peralatan Komputer dan Media Penyimpanan',
                    'toko' => $tokoKomputer,
                    'items' => [
                        ['nama' => 'Flashdisk 32GB', 'qty' => 4, 'harga' => 95000],
                        ['nama' => 'Tinta Printer', 'qty' => 3, 'harga' => 85000],
                    ],
                    'tgl' => $now->copy()->subDays(10),
                    'selisih' => false,
                    'bku' => 'BKU/11/2026/0001',
                ],
            ],

            // Bidang 2: Bidang Pemasaran Pariwisata
            2 => [
                [
                    'status' => 'menunggu_realisasi',
                    'judul' => 'Pengadaan Perlengkapan Promosi Event Geopark Silokek',
                    'toko' => $tokoRilex,
                    'items' => [
                        ['nama' => 'Barang Cetakan - Fotocopy Biasa', 'qty' => 10, 'harga' => 300],
                        ['nama' => 'Kertas HVS Sidu, ukuran F4, 75 gram', 'qty' => 1, 'harga' => 54100],
                    ],
                    'tgl' => $now->copy()->subDays(4),
                    'selisih' => false,
                ],
                [
                    'status' => 'menunggu_verifikasi_pptk',
                    'judul' => 'Belanja Makanan dan Minuman Rapat Koordinasi Promosi Wisata',
                    'toko' => $tokoSinarJaya,
                    'items' => [
                        ['nama' => 'Makanan/Sembako - Kue Kotak isi 3 kue + air', 'qty' => 20, 'harga' => 15000],
                        ['nama' => 'Bahan Masakan/Minuman - Kopi Cap Rangkiang', 'qty' => 2, 'harga' => 28000],
                    ],
                    'tgl' => $now->copy()->subDays(5),
                    'selisih' => false,
                ],
            ],

            // Bidang 3: Pengembangan Destinasi Pariwisata
            3 => [
                [
                    'status' => 'menunggu_verifikasi_ppk',
                    'judul' => 'Pengadaan Komponen Kelistrikan & Lampu Spot Destinasi Wisata',
                    'toko' => $tokoRilex,
                    'items' => [
                        ['nama' => 'Pena Signo Uniball', 'qty' => 3, 'harga' => 23000],
                        ['nama' => 'Flashdisk 32GB', 'qty' => 1, 'harga' => 95000],
                    ],
                    'tgl' => $now->copy()->subDays(6),
                    'selisih' => false,
                ],
                [
                    'status' => 'revisi_pptk',
                    'judul' => 'Penyediaan ATK Perencanaan Tata Ruang Kawasan Wisata',
                    'toko' => $tokoSinarJaya,
                    'items' => [
                        ['nama' => 'Spidol Whiteboard', 'qty' => 6, 'harga' => 75000],
                        ['nama' => 'Kertas HVS A4 80 gram', 'qty' => 5, 'harga' => 55000],
                    ],
                    'tgl' => $now->copy()->subDays(3),
                    'selisih' => false,
                    'catatan_revisi' => 'Mohon sesuaikan rincian spesifikasi spidol dengan kebutuhan teknis tim surveyor lapangan.',
                ],
            ],

            // Bidang 4: Pemuda dan Olahraga
            4 => [
                [
                    'status' => 'menunggu_persetujuan_pa',
                    'judul' => 'Pengadaan ATK & Kelengkapan Administrasi Seleksi Atlet Porprov',
                    'toko' => $tokoSinarJaya,
                    'items' => [
                        ['nama' => 'Pena Signo Uniball', 'qty' => 10, 'harga' => 23000],
                        ['nama' => 'Kertas HVS A4 80 gram', 'qty' => 6, 'harga' => 55000],
                        ['nama' => 'Tinta Printer', 'qty' => 2, 'harga' => 85000],
                    ],
                    'tgl' => $now->copy()->subDays(7),
                    'selisih' => false,
                ],
                [
                    'status' => 'menunggu_verifikasi_bendahara',
                    'judul' => 'Belanja Perlengkapan Dokumentasi Pembinaan Pemuda Pelopor',
                    'toko' => $tokoKomputer,
                    'items' => [
                        ['nama' => 'Flashdisk 32GB', 'qty' => 3, 'harga' => 95000],
                        ['nama' => 'Tinta Printer', 'qty' => 2, 'harga' => 85000],
                    ],
                    'tgl' => $now->copy()->subDays(8),
                    'selisih' => false,
                ],
            ],
        ];

        foreach ($bidangs as $bidang) {
            $skenarios = $skenarioPerBidang[$bidang->id] ?? [];
            if (empty($skenarios)) {
                continue;
            }

            // Tentukan staf pengusul dan PPTK (Kepala Bidang)
            $pptkUser = User::find($bidang->kepala_id) ?? $bidang->users->first() ?? $paUser;
            $stafUsers = $bidang->users->where('id', '!=', $pptkUser->id);
            $stafUser = $stafUsers->first() ?? $pptkUser;

            // Cari kegiatan & sub kegiatan yang sesuai bidang
            $kegiatan = MasterKegiatan::where('bidang_id', $bidang->id)->first()
                ?? MasterKegiatan::first();
            $subKegiatan = $kegiatan ? MasterSubKegiatan::where('kegiatan_id', $kegiatan->id)->first() : null;
            if (!$subKegiatan) {
                $subKegiatan = MasterSubKegiatan::first();
                $kegiatan = $subKegiatan ? MasterKegiatan::find($subKegiatan->kegiatan_id) : null;
            }

            foreach ($skenarios as $idx => $sken) {
                $nomorPengajuan = 'SP/' . $bidang->inisial . '/' . str_pad((string) $now->month, 2, '0', STR_PAD_LEFT) . '/' . $now->year . '/' . str_pad((string) ($idx + 1), 4, '0', STR_PAD_LEFT);
                $status = $sken['status'];
                $toko = $sken['toko'];
                $tglPengajuan = $sken['tgl'];

                // Buat Header Pengajuan SPJ
                $spj = PengajuanSpj::create([
                    'nomor_pengajuan' => $nomorPengajuan,
                    'user_id' => $stafUser->id,
                    'bidang_id' => $bidang->id,
                    'kegiatan_id' => $kegiatan?->id ?? 1,
                    'sub_kegiatan_id' => $subKegiatan?->id ?? 1,
                    'tanggal_pengajuan' => $tglPengajuan->toDateString(),
                    'tanggal_batas_pengiriman' => $tglPengajuan->copy()->addDays(7)->toDateString(),
                    'total_nilai_rencana' => 0,
                    'total_nilai_realisasi' => null,
                    'ppn_persen' => 12.00,
                    'status' => $status,
                    'master_toko_id' => $toko->id,
                    'nama_toko' => $toko->nama_toko,
                    'alamat_toko' => $toko->alamat,
                    'telepon_toko' => $toko->telepon,
                    'nama_bank' => $toko->nama_bank,
                    'metode_pembayaran' => 'Transfer Bank / Tempo 14 Hari setelah barang diterima',
                    'penerima_nama' => $toko->nama_pemilik,
                    'penerima_identitas' => $toko->nomor_rekening,
                    'file_nota_penjual' => in_array($status, ['menunggu_verifikasi_pptk', 'menunggu_verifikasi_ppk', 'menunggu_persetujuan_pa', 'menunggu_verifikasi_bendahara', 'disetujui', 'dicairkan'], true)
                        ? 'simulasi/faktur_toko_' . $counter . '.pdf'
                        : null,
                    'file_dokumentasi_barang' => in_array($status, ['menunggu_verifikasi_pptk', 'menunggu_verifikasi_ppk', 'menunggu_persetujuan_pa', 'menunggu_verifikasi_bendahara', 'disetujui', 'dicairkan'], true)
                        ? 'simulasi/foto_fisik_barang_' . $counter . '.jpg'
                        : null,
                    'justifikasi_selisih' => $sken['justifikasi'] ?? null,
                    'catatan_revisi' => $sken['catatan_revisi'] ?? null,
                    'pptk_user_id' => in_array($status, ['menunggu_realisasi', 'menunggu_verifikasi_pptk', 'revisi_pptk', 'menunggu_verifikasi_ppk', 'menunggu_persetujuan_pa', 'menunggu_verifikasi_bendahara', 'disetujui', 'dicairkan'], true)
                        ? $pptkUser->id
                        : null,
                    'ppk_user_id' => in_array($status, ['menunggu_persetujuan_pa', 'revisi_pa', 'menunggu_verifikasi_bendahara', 'disetujui', 'dicairkan'], true)
                        ? $ppkUser?->id
                        : null,
                    'pa_user_id' => in_array($status, ['menunggu_verifikasi_bendahara', 'disetujui', 'dicairkan'], true)
                        ? $paUser?->id
                        : null,
                    'bendahara_user_id' => $status === 'dicairkan'
                        ? $bendaharaUser?->id
                        : null,
                    'tanggal_dicairkan' => $status === 'dicairkan'
                        ? $tglPengajuan->copy()->addDays(5)->toDateString()
                        : null,
                    'nomor_bku' => $sken['bku'] ?? null,
                ]);

                // Buat Detail Item Barang
                $totalRencana = 0;
                $totalRealisasi = 0;
                $isRealisasiActive = in_array($status, ['menunggu_verifikasi_pptk', 'menunggu_verifikasi_ppk', 'menunggu_persetujuan_pa', 'menunggu_verifikasi_bendahara', 'disetujui', 'dicairkan'], true);

                foreach ($sken['items'] as $it) {
                    $barang = $barangList->firstWhere('nama_barang', $it['nama']) ?? $barangList->first();
                    $rekening = $barang?->kodeRekening ?? $rekeningList->first();
                    $subtotalRencana = $it['qty'] * $it['harga'];

                    $qtyRealisasi = $isRealisasiActive ? $it['qty'] : null;
                    $hargaRealisasi = $isRealisasiActive ? $it['harga'] : null;
                    $subtotalReal = $isRealisasiActive ? $subtotalRencana : null;

                    // Jika diset ada selisih di realisasi
                    if ($isRealisasiActive && $sken['selisih']) {
                        $qtyRealisasi = $it['qty'] + 1;
                        $subtotalReal = $qtyRealisasi * $hargaRealisasi;
                    }

                    $rkaItem = \App\Models\RincianAnggaranBelanja::where('sub_kegiatan_id', $subKegiatan->id)
                        ->where(function ($q) use ($barang) {
                            if ($barang) {
                                $q->where('master_barang_id', $barang->id)
                                  ->orWhere('nama_item', 'LIKE', '%' . $barang->nama_barang . '%');
                            }
                        })
                        ->first();

                    DetailPengajuanSpj::create([
                        'pengajuan_spj_id' => $spj->id,
                        'rincian_anggaran_belanja_id' => $rkaItem?->id,
                        'kode_rekening_id' => $rkaItem?->kode_rekening_id ?? ($rekening?->id ?? 1),
                        'master_barang_id' => $barang?->id,
                        'nama_item_belanja' => $rkaItem?->nama_item ?? ($barang?->nama_barang ?? 'Item Belanja'),
                        'satuan' => $rkaItem?->satuan ?? ($barang?->satuan?->nama ?? 'Unit'),
                        'kuantitas' => $it['qty'],
                        'harga_satuan' => $rkaItem?->harga_satuan ?? $it['harga'],
                        'subtotal' => $subtotalRencana,
                        'kuantitas_realisasi' => $qtyRealisasi,
                        'harga_realisasi' => $hargaRealisasi,
                        'subtotal_realisasi' => $subtotalReal,
                    ]);

                    $totalRencana += $subtotalRencana;
                    if ($subtotalReal) {
                        $totalRealisasi += $subtotalReal;
                    }
                }

                // Update Total pada Header
                $spj->updateQuietly([
                    'total_nilai_rencana' => $totalRencana,
                    'total_nilai_realisasi' => $totalRealisasi > 0 ? $totalRealisasi : null,
                ]);

                // Buat Log Audit Trail (RiwayatStatusSpj)
                RiwayatStatusSpj::create([
                    'pengajuan_spj_id' => $spj->id,
                    'status_sebelumnya' => 'draft',
                    'status_berikutnya' => 'diajukan',
                    'user_id' => $stafUser->id,
                    'peran_saat_tindakan' => 'staf',
                    'catatan' => 'Pengajuan Surat Pesanan dibuat oleh staf bidang.',
                    'created_at' => $tglPengajuan->copy()->addMinutes(10),
                ]);

                if (in_array($status, ['menunggu_realisasi', 'menunggu_verifikasi_pptk', 'revisi_pptk', 'menunggu_verifikasi_ppk', 'menunggu_persetujuan_pa', 'menunggu_verifikasi_bendahara', 'disetujui', 'dicairkan'], true)) {
                    RiwayatStatusSpj::create([
                        'pengajuan_spj_id' => $spj->id,
                        'status_sebelumnya' => 'diajukan',
                        'status_berikutnya' => 'menunggu_realisasi',
                        'user_id' => $pptkUser->id,
                        'peran_saat_tindakan' => 'pptk',
                        'catatan' => 'Surat Pesanan telah disetujui dan ditandatangani oleh PPTK.',
                        'created_at' => $tglPengajuan->copy()->addHours(2),
                    ]);

                    VerifikasiSpj::create([
                        'pengajuan_spj_id' => $spj->id,
                        'tahap' => 'pptk',
                        'user_id' => $pptkUser->id,
                        'checklist_data' => [
                            'sp1' => 'Kesesuaian jenis barang dengan kebutuhan kegiatan bidang',
                            'sp2' => 'Ketersediaan stok barang dan kewajaran volume pesanan',
                            'sp3' => 'Persetujuan penandatanganan Surat Pesanan / Nota Pesanan',
                        ],
                        'catatan' => 'Surat pesanan disetujui untuk dibelanjakan ke rekanan penyedia.',
                        'is_approved' => true,
                        'created_at' => $tglPengajuan->copy()->addHours(2),
                    ]);
                }

                if ($isRealisasiActive) {
                    RiwayatStatusSpj::create([
                        'pengajuan_spj_id' => $spj->id,
                        'status_sebelumnya' => 'menunggu_realisasi',
                        'status_berikutnya' => 'menunggu_verifikasi_pptk',
                        'user_id' => $stafUser->id,
                        'peran_saat_tindakan' => 'staf',
                        'catatan' => 'Realisasi pembelian barang, nota toko, dan dokumentasi fisik berhasil diunggah.',
                        'created_at' => $tglPengajuan->copy()->addDays(2),
                    ]);
                }

                if (in_array($status, ['menunggu_verifikasi_ppk', 'menunggu_persetujuan_pa', 'menunggu_verifikasi_bendahara', 'disetujui', 'dicairkan'], true)) {
                    RiwayatStatusSpj::create([
                        'pengajuan_spj_id' => $spj->id,
                        'status_sebelumnya' => 'menunggu_verifikasi_pptk',
                        'status_berikutnya' => 'menunggu_verifikasi_ppk',
                        'user_id' => $pptkUser->id,
                        'peran_saat_tindakan' => 'pptk',
                        'catatan' => 'Lolos checklist PPTK, berkas diteruskan ke Kasubag Keuangan (PPK).',
                        'created_at' => $tglPengajuan->copy()->addDays(2)->addHours(3),
                    ]);
                }

                if ($status === 'revisi_pptk') {
                    RiwayatStatusSpj::create([
                        'pengajuan_spj_id' => $spj->id,
                        'status_sebelumnya' => 'menunggu_verifikasi_pptk',
                        'status_berikutnya' => 'revisi_pptk',
                        'user_id' => $pptkUser->id,
                        'peran_saat_tindakan' => 'pptk',
                        'catatan' => $sken['catatan_revisi'] ?? 'Perlu perbaikan kelengkapan berkas.',
                        'created_at' => $tglPengajuan->copy()->addDays(2)->addHours(4),
                    ]);
                }

                if (in_array($status, ['menunggu_persetujuan_pa', 'menunggu_verifikasi_bendahara', 'disetujui', 'dicairkan'], true)) {
                    RiwayatStatusSpj::create([
                        'pengajuan_spj_id' => $spj->id,
                        'status_sebelumnya' => 'menunggu_verifikasi_ppk',
                        'status_berikutnya' => 'menunggu_persetujuan_pa',
                        'user_id' => $ppkUser?->id ?? $stafUser->id,
                        'peran_saat_tindakan' => 'ppk',
                        'catatan' => 'Verifikasi pembebanan anggaran & administrasi PPK disetujui, diteruskan ke PA (Kadis).',
                        'created_at' => $tglPengajuan->copy()->addDays(3),
                    ]);
                }

                if (in_array($status, ['menunggu_verifikasi_bendahara', 'disetujui', 'dicairkan'], true)) {
                    RiwayatStatusSpj::create([
                        'pengajuan_spj_id' => $spj->id,
                        'status_sebelumnya' => 'menunggu_persetujuan_pa',
                        'status_berikutnya' => 'menunggu_verifikasi_bendahara',
                        'user_id' => $paUser?->id ?? $stafUser->id,
                        'peran_saat_tindakan' => 'pa',
                        'catatan' => 'Persetujuan akhir Pengguna Anggaran (Kadis) diterbitkan bersama TTD Kwitansi & NPD.',
                        'created_at' => $tglPengajuan->copy()->addDays(4),
                    ]);
                }

                if ($status === 'dicairkan') {
                    RiwayatStatusSpj::create([
                        'pengajuan_spj_id' => $spj->id,
                        'status_sebelumnya' => 'menunggu_verifikasi_bendahara',
                        'status_berikutnya' => 'dicairkan',
                        'user_id' => $bendaharaUser?->id ?? $stafUser->id,
                        'peran_saat_tindakan' => 'bendahara',
                        'catatan' => 'Dana belanja telah dibayarkan lunas oleh Bendahara Pengeluaran. SPJ sah dan tercatat di BKU.',
                        'created_at' => $tglPengajuan->copy()->addDays(5),
                    ]);
                }

                $this->command->info("Simulasi [{$bidang->nama}] #{$counter}: {$spj->nomor_pengajuan} - Status: {$status} ({$sken['judul']})");
                $counter++;
            }
        }

        $this->command->info('Seeder Pengajuan SPJ simulasi (2 per bidang = 8 total) berhasil dijalankan.');
    }
}
