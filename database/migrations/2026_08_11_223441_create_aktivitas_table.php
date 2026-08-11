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
        Schema::create('aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatans')->cascadeOnDelete();
            $table->foreignId('bidang_id')->nullable()->constrained('bidangs')->cascadeOnDelete();
            $table->string('kode')->nullable();
            $table->string('nama_aktivitas');
            $table->integer('menit')->default(0);
            $table->unique(['jabatan_id', 'bidang_id', 'nama_aktivitas'], 'aktivitas_jabatan_bidang_nama_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktivitas');
    }
};
