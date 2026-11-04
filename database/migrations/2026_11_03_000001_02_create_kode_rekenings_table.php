<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kode_rekenings', function (Blueprint $table) {
            $table->id();
            $table->string('akun')->index();
            $table->string('kelompok')->index();
            $table->string('jenis')->index();
            $table->string('objek')->index();
            $table->string('rincian_objek')->index();
            $table->string('sub_rincian_objek')->index();
            $table->string('sub_sub_rincian_objek')->index();
            $table->string('kode_lengkap')->index();
            $table->string('uraian')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kode_rekenings');
    }
};

