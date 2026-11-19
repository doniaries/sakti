<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita_acara_bmds', function (Blueprint $table): void {
            $table->id();
            $table->string('jenis', 30)->default('penggunaan')->index(); // penggunaan | peminjaman
            $table->string('nomor_surat', 100)->index();
            $table->date('tanggal');

            $table->foreignId('pihak_pertama_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('pihak_pertama_nama');
            $table->string('pihak_pertama_nip', 50)->nullable();
            $table->string('pihak_pertama_pangkat', 100)->nullable();
            $table->string('pihak_pertama_jabatan')->nullable();
            $table->string('pihak_pertama_instansi')->default('Dinas Pariwisata Pemuda dan Olahraga');

            $table->foreignId('pihak_kedua_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('pihak_kedua_nama');
            $table->string('pihak_kedua_nip', 50)->nullable();
            $table->string('pihak_kedua_pangkat', 100)->nullable();
            $table->string('pihak_kedua_jabatan')->nullable();
            $table->string('pihak_kedua_instansi')->default('Dinas Pariwisata Pemuda dan Olahraga');

            $table->string('kondisi_barang', 50)->default('Baik');
            $table->string('lokasi_penggunaan')->default('Muaro Sijunjung');
            $table->text('keterangan')->nullable();

            $table->string('kepala_dinas_nama')->default('AFRINELDI, SH');
            $table->string('kepala_dinas_nip', 50)->default('19720407 199202 1 002');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('berita_acara_bmd_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('berita_acara_bmd_id')->constrained('berita_acara_bmds')->cascadeOnDelete();
            $table->foreignId('buku_inventaris_id')->nullable()->constrained('buku_inventaris')->nullOnDelete();
            $table->string('nama_barang');
            $table->integer('jumlah')->default(1);
            $table->string('satuan', 50)->default('Unit');
            $table->decimal('nilai_perolehan', 15, 2)->default(0);
            $table->string('lokasi_penggunaan')->default('Muaro Sijunjung');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita_acara_bmd_items');
        Schema::dropIfExists('berita_acara_bmds');
    }
};
