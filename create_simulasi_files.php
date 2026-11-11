<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\PengajuanSpj;

// Pastikan folder simulasi ada di public disk
Storage::disk('public')->makeDirectory('simulasi');

$spjs = PengajuanSpj::with(['details.masterBarang', 'masterToko'])->get();

foreach ($spjs as $counter => $spj) {
    $idx = $counter + 1;
    $tokoName = $spj->nama_toko ?: ($spj->masterToko?->nama_toko ?? 'Toko Mitra Resmi');
    $tokoAlamat = $spj->masterToko?->alamat ?? 'Pasar Inpres Muaro Sijunjung';
    $tokoHp = $spj->masterToko?->telepon ?? '0812-6789-1234';
    $tokoPimpinan = $spj->masterToko?->nama_pemilik ?? 'Hendra Putra';

    // 1. Buat Faktur Toko PDF
    $itemsHtml = '';
    $subtotal = 0;
    foreach ($spj->details as $dIndex => $detail) {
        $nama = $detail->masterBarang?->nama_barang ?? 'Barang Kebutuhan Kegiatan';
        $qty = $detail->kuantitas;
        $satuan = $detail->masterBarang?->satuan?->nama ?? 'Unit';
        $harga = (float) $detail->harga_satuan;
        $tot = (float) $detail->subtotal;
        $subtotal += $tot;
        $itemsHtml .= "<tr>
            <td style='border:1px solid #333; padding:5px; text-align:center;'>" . ($dIndex + 1) . "</td>
            <td style='border:1px solid #333; padding:5px;'>{$nama}</td>
            <td style='border:1px solid #333; padding:5px; text-align:center;'>{$qty} {$satuan}</td>
            <td style='border:1px solid #333; padding:5px; text-align:right;'>Rp " . number_format($harga, 0, ',', '.') . "</td>
            <td style='border:1px solid #333; padding:5px; text-align:right;'>Rp " . number_format($tot, 0, ',', '.') . "</td>
        </tr>";
    }

    $ppn = (float) $spj->nilai_ppn;
    $grandTotal = $subtotal + $ppn;

    $pdfHtml = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <style>
            body { font-family: Arial, sans-serif; font-size: 10pt; color: #111; margin: 20px; }
            .header { border-bottom: 2px solid #222; padding-bottom: 8px; margin-bottom: 12px; }
            .title { text-align: center; font-size: 14pt; font-weight: bold; margin: 0; }
            .subtitle { text-align: center; font-size: 9pt; color: #555; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th { border: 1px solid #333; padding: 6px; background-color: #eee; font-weight: bold; text-align: center; }
            .stempel { width: 140px; border: 2px solid #0056b3; color: #0056b3; font-weight: bold; text-align: center; padding: 8px; margin-top: 15px; border-radius: 8px; transform: rotate(-5deg); display: inline-block; }
        </style>
    </head>
    <body>
        <div class='header'>
            <div class='title'>{$tokoName}</div>
            <div class='subtitle'>{$tokoAlamat} | Telp: {$tokoHp}</div>
        </div>
        <div style='text-align:center; font-weight:bold; font-size:12pt; text-decoration:underline;'>FAKTUR / NOTA ASLI PENJUALAN</div>
        <table style='margin-bottom:10px;'>
            <tr>
                <td style='width:60%;'>
                    <strong>Kepada Yth:</strong><br>
                    Bendahara Pengeluaran / PPTK Dinas<br>
                    Untuk Kegiatan: {$spj->nomor_pengajuan}
                </td>
                <td style='width:40%; text-align:right;'>
                    No. Faktur: FKT/" . date('Y/m/') . str_pad($idx, 4, '0', STR_PAD_LEFT) . "<br>
                    Tanggal: " . date('d F Y', strtotime($spj->tanggal_pengajuan ?? 'now')) . "
                </td>
            </tr>
        </table>
        <table>
            <thead>
                <tr>
                    <th style='width:6%;'>No</th>
                    <th>Nama Barang / Deskripsi</th>
                    <th style='width:15%;'>Banyaknya</th>
                    <th style='width:22%;'>Harga Satuan</th>
                    <th style='width:22%;'>Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                {$itemsHtml}
            </tbody>
            <tfoot>
                <tr>
                    <td colspan='4' style='border:1px solid #333; padding:5px; text-align:right; font-weight:bold;'>Subtotal</td>
                    <td style='border:1px solid #333; padding:5px; text-align:right; font-weight:bold;'>Rp " . number_format($subtotal, 0, ',', '.') . "</td>
                </tr>
                " . ($ppn > 0 ? "<tr>
                    <td colspan='4' style='border:1px solid #333; padding:5px; text-align:right;'>PPN (11%)</td>
                    <td style='border:1px solid #333; padding:5px; text-align:right;'>Rp " . number_format($ppn, 0, ',', '.') . "</td>
                </tr>" : "") . "
                <tr>
                    <td colspan='4' style='border:1px solid #333; padding:5px; text-align:right; font-weight:bold; font-size:11pt;'>TOTAL PEMBAYARAN</td>
                    <td style='border:1px solid #333; padding:5px; text-align:right; font-weight:bold; font-size:11pt;'>Rp " . number_format($grandTotal, 0, ',', '.') . "</td>
                </tr>
            </tfoot>
        </table>

        <table style='margin-top:20px;'>
            <tr>
                <td style='width:50%;'>
                    <div class='stempel'>
                        LUNAS<br>
                        " . strtoupper($tokoName) . "<br>
                        TERVERIFIKASI
                    </div>
                </td>
                <td style='width:50%; text-align:center;'>
                    Muaro Sijunjung, " . date('d F Y', strtotime($spj->tanggal_pengajuan ?? 'now')) . "<br>
                    Hormat Kami,<br>
                    <strong>{$tokoName}</strong>
                    <br><br><br><br>
                    <u>( {$tokoPimpinan} )</u>
                </td>
            </tr>
        </table>
    </body>
    </html>
    ";

    $pdfContent = Pdf::loadHTML($pdfHtml)->setPaper('a4', 'portrait')->output();
    Storage::disk('public')->put("simulasi/faktur_toko_{$idx}.pdf", $pdfContent);
    echo "Generated simulasi/faktur_toko_{$idx}.pdf\n";

    // 2. Buat Foto Dokumentasi Barang JPEG
    $img = imagecreatetruecolor(800, 600);
    $bg = imagecolorallocate($img, 240, 245, 250);
    $headerColor = imagecolorallocate($img, 15, 76, 129);
    $textColor = imagecolorallocate($img, 33, 37, 41);
    $badgeBg = imagecolorallocate($img, 40, 167, 69);
    $white = imagecolorallocate($img, 255, 255, 255);
    $border = imagecolorallocate($img, 200, 210, 220);

    imagefill($img, 0, 0, $bg);
    imagerectangle($img, 10, 10, 789, 589, $border);
    imagerectangle($img, 12, 12, 787, 587, $border);

    // Header bar
    imagefilledrectangle($img, 15, 15, 785, 75, $headerColor);
    imagestring($img, 5, 30, 25, "DOKUMENTASI FISIK PENERIMAAN BARANG", $white);
    imagestring($img, 3, 30, 48, "Kegiatan SPJ: " . $spj->nomor_pengajuan . " | " . $tokoName, $white);

    // Badge
    imagefilledrectangle($img, 600, 28, 770, 60, $badgeBg);
    imagestring($img, 4, 615, 36, "KONDISI BAIK 100%", $white);

    // Item box
    imagefilledrectangle($img, 30, 100, 770, 520, $white);
    imagerectangle($img, 30, 100, 770, 520, $border);

    // Text details inside
    imagestring($img, 5, 50, 130, "BERITA ACARA FOTO SERAH TERIMA FISIK BARANG:", $textColor);
    $y = 170;
    foreach ($spj->details as $dIndex => $detail) {
        $nama = $detail->masterBarang?->nama_barang ?? 'Barang Kebutuhan Kegiatan';
        $qty = $detail->kuantitas;
        $satuan = $detail->masterBarang?->satuan?->nama ?? 'Unit';
        imagestring($img, 4, 60, $y, ($dIndex + 1) . ". {$nama} [ Volume: {$qty} {$satuan} ] - Lengkap & Berfungsi", $textColor);
        $y += 35;
        if ($y > 450) break;
    }

    imagestring($img, 3, 50, 480, "Lokasi Penerimaan: Kantor Dinas Pariwisata Pemuda dan Olah Raga Kabupaten Sijunjung", $textColor);
    imagestring($img, 3, 50, 500, "Waktu Dokumentasi: " . date('d-m-Y H:i') . " WIB | Pemeriksa: Tim Penerima Barang & PPTK", $textColor);

    // Footer
    imagestring($img, 2, 30, 540, "Sakti Keuangan - Sistem Administrasi Kinerja Terintegrasi", $headerColor);

    ob_start();
    imagejpeg($img, null, 90);
    $imgData = ob_get_clean();
    imagedestroy($img);

    Storage::disk('public')->put("simulasi/foto_fisik_barang_{$idx}.jpg", $imgData);
    echo "Generated simulasi/foto_fisik_barang_{$idx}.jpg\n";
}

echo "Semua file simulasi berhasil dibuat!\n";

