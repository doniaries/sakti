<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('buku_inventaris', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nomor_urut')->index();
            $table->string('kode_barang', 50)->index();
            $table->string('register', 100)->nullable()->index();
            $table->string('nama_barang', 255);
            $table->string('merk_type', 255)->nullable();
            $table->text('nomor_sertifikat_pabrik_chasis_mesin')->nullable();
            $table->string('bahan', 100)->nullable();
            $table->string('asal_perolehan', 100)->default('Pembelian');
            $table->unsignedSmallInteger('tahun_pembelian')->nullable()->index();
            $table->string('ukuran_konstruksi', 100)->nullable();
            $table->string('satuan', 50)->nullable();
            $table->string('keadaan_barang', 50)->default('Baik')->index();
            $table->unsignedInteger('jumlah_barang')->default(1);
            $table->decimal('harga', 15, 2)->default(0);
            $table->text('keterangan')->nullable();

            $table->string('bidang', 150)->default('Bidang Pariwisata');
            $table->string('unit_organisasi', 150)->default('DINAS PARIWISATA, PEMUDA DAN OLAH RAGA');
            $table->string('sub_unit_organisasi', 150)->default('DINAS PARIWISATA, PEMUDA DAN OLAH RAGA');
            $table->string('upb', 150)->default('DINAS PARIWISATA, PEMUDA DAN OLAH RAGA');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buku_inventaris');
    }
};
