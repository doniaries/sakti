<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_tugas', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat', 100)->unique();
            $table->string('nama_kegiatan');
            $table->text('dasar_surat')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('lokasi')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('penandatangan_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('surat_tugas_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_tugas_id')->constrained('surat_tugas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('peran_tugas', 150)->nullable();
            $table->timestamps();

            $table->unique(['surat_tugas_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_tugas_user');
        Schema::dropIfExists('surat_tugas');
    }
};
