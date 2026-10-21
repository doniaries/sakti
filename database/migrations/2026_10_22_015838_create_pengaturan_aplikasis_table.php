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
        Schema::create('pengaturan_aplikasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_aplikasi')->default('ABSENSI LHK');
            $table->string('sub_nama')->nullable()->default('Dinas Lingkungan Hidup');
            $table->string('logo')->nullable();
            $table->string('versi_aplikasi')->default('v1.0.0');
            $table->string('pembuat_aplikasi')->nullable()->default('Diskominfotik');
            $table->string('kontak_bantuan')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_aplikasis');
    }
};
