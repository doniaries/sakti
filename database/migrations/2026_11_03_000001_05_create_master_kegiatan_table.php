<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable()->constrained('master_programs')->nullOnDelete();
            $table->integer('tahun_anggaran')->default((int) date('Y'))->index();
            $table->string('kode_kegiatan')->unique();
            $table->string('nama_kegiatan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kegiatan');
    }
};
