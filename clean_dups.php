<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Daftar 12 rekening resmi standar SKPD (3 digit & 5 digit)
$standarResmi = [
    '5.1.02.01.001.00024' => '5.1.02.01.01.0024',
    '5.1.02.01.001.00025' => '5.1.02.01.01.0025',
    '5.1.02.01.001.00026' => '5.1.02.01.01.0026',
    '5.1.02.01.001.00029' => '5.1.02.01.01.0029',
    '5.1.02.01.001.00030' => '5.1.02.01.01.0030',
    '5.1.02.01.001.00043' => '5.1.02.01.01.0043',
    '5.1.02.01.001.00052' => '5.1.02.01.01.0052',
    '5.1.02.01.001.00058' => '5.1.02.01.01.0058',
    '5.1.02.01.001.00004' => '5.1.02.01.01.0004',
    '5.1.02.04.001.00003' => '5.1.02.04.01.0003',
    '5.1.02.02.001.00025' => '5.1.02.02.01.0025',
    '5.1.02.02.001.00048' => '5.1.02.02.01.0048',
];

echo "Mengecek duplikasi di database:\n";
foreach ($standarResmi as $resmi => $duplikat) {
    $rowResmi = \App\Models\KodeRekening::where('kode_lengkap', $resmi)->first();
    $rowDup = \App\Models\KodeRekening::where('kode_lengkap', $duplikat)->first();
    
    echo "- Resmi: {$resmi} (" . ($rowResmi ? "ID {$rowResmi->id}" : "TIDAK ADA") . ") | Dup: {$duplikat} (" . ($rowDup ? "ID {$rowDup->id}" : "TIDAK ADA") . ")\n";
    
    if ($rowDup) {
        // Alihkan foreign key jika ada relasi yang terlanjur terhubung ke rowDup
        if ($rowResmi) {
            \App\Models\MasterBarang::where('kode_rekening_id', $rowDup->id)->update(['kode_rekening_id' => $rowResmi->id]);
            \App\Models\RincianAnggaranBelanja::where('kode_rekening_id', $rowDup->id)->update(['kode_rekening_id' => $rowResmi->id]);
            \App\Models\DetailPengajuanSpj::where('kode_rekening_id', $rowDup->id)->update(['kode_rekening_id' => $rowResmi->id]);
            
            $rowDup->delete();
            echo "  --> BERHASIL DIHAPUS: ID {$rowDup->id} ({$duplikat})\n";
        }
    }
}
