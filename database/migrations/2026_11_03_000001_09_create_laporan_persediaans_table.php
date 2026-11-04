<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_persediaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_id')->constrained('bidangs')->cascadeOnDelete();
            $table->foreignId('master_barang_id')->constrained('master_barangs')->cascadeOnDelete();
            $table->integer('bulan');
            $table->integer('tahun');
            $table->integer('saldo_awal')->default(0);
            $table->integer('jumlah_masuk')->default(0);
            $table->decimal('nilai_masuk', 15, 2)->default(0);
            $table->integer('jumlah_keluar')->default(0);
            $table->decimal('nilai_keluar', 15, 2)->default(0);
            $table->integer('jumlah_expired')->default(0);
            $table->decimal('nilai_expired', 15, 2)->default(0);
            $table->integer('saldo_akhir')->default(0);
            $table->decimal('nilai_akhir', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['bidang_id', 'master_barang_id', 'bulan', 'tahun'], 'lap_brg_bln_thn_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_persediaans');
    }
};

