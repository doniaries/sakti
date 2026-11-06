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
        Schema::create('riwayat_pendidikans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('jenjang', 20); // SD, SLTP, SLTA, D1, D2, D3, D4, S1, S2, S3
            $table->string('nama_institusi'); // Nama Sekolah / Universitas
            $table->string('jurusan')->nullable(); // Jurusan / Program Studi
            $table->unsignedSmallInteger('tahun_lulus')->nullable(); // Tahun Lulus
            $table->string('nomor_ijazah')->nullable();
            $table->string('file_ijazah')->nullable(); // Path PDF berkas ijazah
            $table->boolean('is_pendidikan_terakhir')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'jenjang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pendidikans');
    }
};
