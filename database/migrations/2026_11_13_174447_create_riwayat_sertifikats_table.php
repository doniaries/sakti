<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('riwayat_sertifikats')) {
            Schema::create('riwayat_sertifikats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('nama_sertifikat');
                $table->string('jenis', 50)->default('sertifikat');
                $table->string('penyelenggara')->nullable();
                $table->string('nomor_sertifikat', 120)->nullable();
                $table->date('tanggal_terbit')->nullable();
                $table->string('file_sertifikat')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'tanggal_terbit']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_sertifikats');
    }
};
