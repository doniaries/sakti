<?php

use App\Models\PengaturanAplikasi;

$p = PengaturanAplikasi::first();
$p->nama_aplikasi = 'SAKTI';
$p->sub_nama = 'Sistem Administrasi, Kinerja, dan Tata Informasi';
$p->nama_dinas = 'Dinas Lingkungan Hidup';
$p->save();

echo "Updated: " . $p->nama_aplikasi . " | " . $p->sub_nama . " | " . $p->nama_dinas . PHP_EOL;
