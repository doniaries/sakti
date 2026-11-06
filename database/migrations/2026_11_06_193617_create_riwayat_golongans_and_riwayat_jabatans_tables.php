<?php

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
        // 1. Tabel Riwayat Pangkat / Golongan MyASN
        Schema::create('riwayat_golongans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('golongan', 20); // IV/a, III/d, dst.
            $table->string('nama_pangkat')->nullable(); // Pembina, Penata Tingkat I, dst.
            $table->string('nomor_sk')->nullable(); // 823.4/1136/BKD-2026
            $table->date('tanggal_sk')->nullable();
            $table->date('tmt_golongan')->nullable(); // 1 April 2026
            $table->string('file_sk_golongan')->nullable(); // Path PDF dokumen SK Pangkat
            $table->boolean('is_golongan_terakhir')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'golongan']);
        });

        // 2. Tabel Riwayat Jabatan MyASN
        Schema::create('riwayat_jabatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_jabatan'); // KEPALA BIDANG PEMASARAN PARIWISATA
            $table->string('jenis_jabatan', 50)->default('Jabatan Struktural'); // Struktural, Fungsional, Pelaksana
            $table->string('unit_kerja')->nullable(); // Nama Instansi / Unit Kerja
            $table->string('nomor_sk')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->date('tmt_jabatan')->nullable(); // TMT Jabatan
            $table->string('file_sk_jabatan')->nullable(); // Path PDF dokumen SK Jabatan
            $table->boolean('is_jabatan_sekarang')->default(false);
            $table->timestamps();

            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_jabatans');
        Schema::dropIfExists('riwayat_golongans');
    }
};
