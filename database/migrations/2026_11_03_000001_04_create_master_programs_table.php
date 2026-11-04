<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_programs', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun_anggaran')->default((int) date('Y'))->index();
            $table->string('kode_program', 20)->index();
            $table->string('nama_program');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['tahun_anggaran', 'kode_program'], 'prog_thn_kode_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_programs');
    }
};

