<?php

namespace Database\Seeders;

use App\Enums\NamaBank;
use App\Models\Bidang;
use App\Models\DetailPengajuanSpj;
use App\Models\MasterBarang;
use App\Models\MasterKegiatan;
use App\Models\MasterSubKegiatan;
use App\Models\MasterToko;
use App\Models\PengajuanSpj;
use App\Models\RincianAnggaranBelanja;
use App\Models\RiwayatStatusSpj;
use App\Models\User;
use App\Models\VerifikasiSpj;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengajuanSpjSeeder extends Seeder
{
    /**
     * Seeder Pengajuan SPJ Komprehensif
     * Mensimulasikan transaksi BEBAS PAJAK (<= Rp 2 Jt) dan KENA PAJAK (> Rp 2 Jt)
     * di seluruh 4 Bidang Dinas dengan tahapan workflow lengkap.
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

        // 3. Bersihkan data SPJ lama agar simulasi segar
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DetailPengajuanSpj::truncate();
        RiwayatStatusSpj::truncate();
        VerifikasiSpj::truncate();
        PengajuanSpj::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $bidangs = Bidang::with('users')->get();
        $now = Carbon::now();

        /**
         * Skenario Simulasi: 2 SPJ per Bidang
         * SPJ 1: Belanja <= Rp 2.000.000 (BEBAS PAJAK: PPN 0%, PPh 0%)
         * SPJ 2: Belanja > Rp 2.000.000 (KENA PAJAK: PPN 11%, PPh 22 1.5%)
         */
        $skenarioPerBidang = [
            // =========================================================================
            // BIDANG 1: SEKRETARIAT (Inisial: Sekre)
            // =========================================================================
            1 => [
                // SPJ 1: Bebas Pajak (Total: Rp 825.000 <= 2 Jt)
                [
                    'status' => 'diajukan',
                    'judul' => 'Pengadaan Kertas & ATK Rutin Administrasi Sekretariat',
                    'toko' => $tokoSinarJaya,
                    'tgl' => $now->copy()->subDays(2),
                    'is_taxable' => false,
                    'items' => [
                        ['nama_item' => 'Kertas HVS A4 80 gram Sidu', 'qty' => 10, 'harga' => 55000], // 550.000
                        ['nama_item' => 'Pena Signo Uniball 0.5', 'qty' => 10, 'harga' => 23000],       // 230.000
                        ['nama_item' => 'Buku Kwitansi Keuangan NCR 5 Ply', 'qty' => 1, 'harga' => 45000], // 45.000
                    ],
                ],
                // SPJ 2: Kena Pajak (Total: Rp 3.850.000 > 2 Jt)
                [
                    'status' => 'dicairkan',
                    'judul' => 'Pengadaan Tinta Printer & Flashdisk Penyimpanan Neraca Keuangan',
                    'toko' => $tokoKomputer,
                    'tgl' => $now->copy()->subDays(12),
                    'is_taxable' => true,
                    'bku' => 'BKU/11/2026/0001',
                    'items' => [
                        ['nama_item' => 'Tinta Printer Epson 001 Black', 'qty' => 10, 'harga' => 150000], // 1.500.000
                        ['nama_item' => 'Tinta Printer Epson 001 Color (Cyan/Magenta/Yellow)', 'qty' => 10, 'harga' => 130000], // 1.300.000
                        ['nama_item' => 'Flashdisk Sandisk 32GB USB 3.0', 'qty' => 11, 'harga' => 95000], // 1.045.000
                    ],
                ],
            ],

            // =========================================================================
            // BIDANG 2: PEMASARAN PARIWISATA (Inisial: PP)
            // =========================================================================
            2 => [
                // SPJ 1: Bebas Pajak (Total: Rp 356.400 <= 2 Jt)
                [
                    'status' => 'menunggu_realisasi',
                    'judul' => 'Belanja Bahan Promosi Cetakan Brosur Geopark Silokek',
                    'toko' => $tokoRilex,
                    'tgl' => $now->copy()->subDays(4),
                    'is_taxable' => false,
                    'items' => [
                        ['nama_item' => 'Barang Cetakan - Fotocopy Biasa', 'qty' => 300, 'harga' => 300], // 90.000
                        ['nama_item' => 'Kertas HVS Sidu, ukuran F4, 75 gram', 'qty' => 4, 'harga' => 65500], // 262.000
                    ],
                ],
                // SPJ 2: Kena Pajak (Total: Rp 5.230.000 > 2 Jt)
                [
                    'status' => 'menunggu_verifikasi_pptk',
                    'judul' => 'Belanja Konsumsi & Jamuan Rapat Koordinasi Promosi Wisata Daerah',
                    'toko' => $tokoSinarJaya,
                    'tgl' => $now->copy()->subDays(5),
                    'is_taxable' => true,
                    'items' => [
                        ['nama_item' => 'Makanan/Sembako - Kue Kotak isi 3 kue + air', 'qty' => 150, 'harga' => 13300], // 1.995.000
                        ['nama_item' => 'Bahan Masakan/Minuman - Kopi Cap Rangkiang', 'qty' => 10, 'harga' => 157750], // 1.577.500
                        ['nama_item' => 'Makanan - Nasi Bungkus 1 Protein', 'qty' => 70, 'harga' => 24000], // 1.680.000
                    ],
                ],
            ],

            // =========================================================================
            // BIDANG 3: PENGEMBANGAN DESTINASI PARIWISATA (Inisial: PDP)
            // =========================================================================
            3 => [
                // SPJ 1: Bebas Pajak (Total: Rp 1.485.000 <= 2 Jt)
                [
                    'status' => 'revisi_pptk',
                    'judul' => 'Pengadaan Cat & Kelengkapan Perawatan Signboard Kawasan Wisata',
                    'toko' => $tokoRilex,
                    'tgl' => $now->copy()->subDays(3),
                    'is_taxable' => false,
                    'catatan_revisi' => 'Mohon tambahkan rincian warna cat sesuai brand identity Geopark.',
                    'items' => [
                        ['nama_item' => 'Cat Kayu & Besi Anti Karat Signboard Wisata', 'qty' => 15, 'harga' => 95000], // 1.425.000
                    ],
                ],
                // SPJ 2: Kena Pajak (Total: Rp 5.350.000 > 2 Jt)
                [
                    'status' => 'menunggu_verifikasi_ppk',
                    'judul' => 'Pengadaan Lampu Sorot LED & Kabel Instalasi Penerangan Wisata',
                    'toko' => $tokoRilex,
                    'tgl' => $now->copy()->subDays(6),
                    'is_taxable' => true,
                    'items' => [
                        ['nama_item' => 'Lampu Sorot LED Outdoor Spot Destinasi 50W', 'qty' => 10, 'harga' => 275000], // 2.750.000
                        ['nama_item' => 'Kabel Listrik NYY 2x2.5mm Outdoor', 'qty' => 4, 'harga' => 650000], // 2.600.000
                    ],
                ],
            ],

            // =========================================================================
            // BIDANG 4: PEMUDA DAN OLAHRAGA (Inisial: PO)
            // =========================================================================
            4 => [
                // SPJ 1: Bebas Pajak (Total: Rp 1.540.000 <= 2 Jt)
                [
                    'status' => 'menunggu_persetujuan_pa',
                    'judul' => 'Pengadaan Bola Pertandingan Latihan Atlet Cabor Daerah',
                    'toko' => $tokoSinarJaya,
                    'tgl' => $now->copy()->subDays(7),
                    'is_taxable' => false,
                    'items' => [
                        ['nama_item' => 'Bola Sepak Standar Pertandingan FIFA Match', 'qty' => 2, 'harga' => 385000], // 770.000
                        ['nama_item' => 'Bola Voli Standar Turnamen Mikasa V200W', 'qty' => 1, 'harga' => 420000],  // 420.000
                        ['nama_item' => 'Rompi Tim Latihan Atlet Porprov', 'qty' => 1, 'harga' => 240000],          // 240.000
                    ],
                ],
                // SPJ 2: Kena Pajak (Total: Rp 5.750.000 > 2 Jt)
                [
                    'status' => 'menunggu_verifikasi_bendahara',
                    'judul' => 'Pengadaan Medali Kejuaraan & Kaos Kontingen Atlet Porprov',
                    'toko' => $tokoSinarJaya,
                    'tgl' => $now->copy()->subDays(8),
                    'is_taxable' => true,
                    'items' => [
                        ['nama_item' => 'Medali Kejuaraan Olahraga Daerah Set Emas/Perak/Perunggu', 'qty' => 20, 'harga' => 175000], // 3.500.000
                        ['nama_item' => 'Kaos Kontingen Atlet Kabupaten Sijunjung', 'qty' => 18, 'harga' => 125000],                // 2.250.000
                    ],
                ],
            ],
        ];

        $counter = 1;

        foreach ($bidangs as $bidang) {
            $skenarios = $skenarioPerBidang[$bidang->id] ?? [];
            if (empty($skenarios)) {
                continue;
            }

            // Staf pengusul dan PPTK (Kepala Bidang)
            $pptkUser = User::find($bidang->kepala_id) ?? $bidang->users->first() ?? $paUser;
            $stafUsers = $bidang->users->where('id', '!=', $pptkUser->id);
            $stafUser = $stafUsers->first() ?? $pptkUser;

            // Cari kegiatan & sub kegiatan yang sesuai bidang
            $kegiatan = MasterKegiatan::where('bidang_id', $bidang->id)->first()
                ?? MasterKegiatan::first();
            $subKegiatan = $kegiatan ? MasterSubKegiatan::where('kegiatan_id', $kegiatan->id)->first() : null;
            if (! $subKegiatan) {
                $subKegiatan = MasterSubKegiatan::first();
                $kegiatan = $subKegiatan ? MasterKegiatan::find($subKegiatan->kegiatan_id) : null;
            }

            foreach ($skenarios as $idx => $sken) {
                $tglPengajuan = $sken['tgl'];
                $nomorPengajuan = PengajuanSpj::generateNomorPengajuan($bidang->id, (int) $now->year, $tglPengajuan->toDateString());
                $status = $sken['status'];
                $toko = $sken['toko'];
                $isTaxable = (bool) $sken['is_taxable'];

                // Pajak: Jika belanja <= 2 Jt, PPN 0% & PPh 0%. Jika > 2 Jt, PPN 11% & PPh 22 1.5%
                $ppnPersen = $isTaxable ? 11.00 : 0.00;
                $pph22Persen = $isTaxable ? 1.50 : 0.00;

                // Buat Header Pengajuan SPJ
                $spj = PengajuanSpj::create([
                    'nomor_pengajuan' => $nomorPengajuan,
                    'user_id' => $stafUser->id,
                    'bidang_id' => $bidang->id,
                    'kegiatan_id' => $kegiatan?->id ?? 1,
                    'sub_kegiatan_id' => $subKegiatan?->id ?? 1,
                    'tahun_anggaran' => (int) $now->year,
                    'metode_pengadaan' => 'non_katalog',
                    'jenis_belanja' => 'barang_jasa',
                    'tanggal_pengajuan' => $tglPengajuan->toDateString(),
                    'tanggal_batas_pengiriman' => $tglPengajuan->copy()->addDays(7)->toDateString(),
                    'total_nilai_rencana' => 0,
                    'total_nilai_realisasi' => null,
                    'ppn_persen' => $ppnPersen,
                    'pph22_persen' => $pph22Persen,
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
                    // Cari item RKA yang cocok
                    $rkaItem = RincianAnggaranBelanja::where('nama_item', 'LIKE', '%' . $it['nama_item'] . '%')->first();

                    $masterBarangId = $rkaItem?->master_barang_id;
                    if (! $masterBarangId) {
                        $mb = MasterBarang::where('nama_barang', 'LIKE', '%' . $it['nama_item'] . '%')->first();
                        $masterBarangId = $mb?->id;
                    }

                    $subtotalRencana = $it['qty'] * $it['harga'];
                    $qtyReal = $isRealisasiActive ? $it['qty'] : null;
                    $hargaReal = $isRealisasiActive ? $it['harga'] : null;
                    $subtotalReal = $isRealisasiActive ? $subtotalRencana : null;

                    DetailPengajuanSpj::create([
                        'pengajuan_spj_id' => $spj->id,
                        'rincian_anggaran_belanja_id' => $rkaItem?->id,
                        'kode_rekening_id' => $rkaItem?->kode_rekening_id ?? 1,
                        'master_barang_id' => $masterBarangId,
                        'nama_item_belanja' => $rkaItem?->nama_item ?? $it['nama_item'],
                        'satuan' => $rkaItem?->satuan ?? 'Unit',
                        'kuantitas' => $it['qty'],
                        'harga_satuan' => $it['harga'],
                        'subtotal' => $subtotalRencana,
                        'kuantitas_realisasi' => $qtyReal,
                        'harga_realisasi' => $hargaReal,
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

                // Buat Riwayat Status Audit Trail
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
                        'created_at' => $tglPengajuan->copy()->addHours(2),
                    ]);
                }

                $counter++;
            }
        }

        $this->command->info('Seeder Pengajuan SPJ berhasil: 8 pengajuan SPJ simulasi (Bebas Pajak & Kena Pajak) untuk 4 Bidang telah siap diuji.');
    }
}
