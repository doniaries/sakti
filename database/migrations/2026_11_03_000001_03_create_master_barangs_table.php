<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kode_rekening_id')->nullable()->constrained('kode_rekenings')->restrictOnDelete();
            $table->string('kode_barang')->index();
            $table->string('uraian')->nullable()->index();
            $table->string('nama_barang');
            $table->foreignId('satuan_id')->nullable()->constrained('satuans')->restrictOnDelete();
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kode_rekening_id', 'nama_barang'], 'mbrg_rek_nama_idx');
            $table->index(['nama_barang', 'kode_barang'], 'mbrg_nama_kode_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_barangs');
    }
};

