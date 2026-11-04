<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_id')->constrained('bidangs')->cascadeOnDelete();
            $table->foreignId('master_barang_id')->constrained('master_barangs')->cascadeOnDelete();
            $table->enum('jenis_transaksi', ['masuk', 'keluar']);
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('nilai_barang', 15, 2)->default(0);
            $table->date('tanggal_transaksi');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['bidang_id', 'master_barang_id', 'tanggal_transaksi'], 'trx_brg_tgl_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_barangs');
    }
};

