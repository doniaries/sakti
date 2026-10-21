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
        Schema::create('hari_liburs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('nama'); // e.g. "Hari Kemerdekaan RI", "Cuti Bersama Idul Fitri"
            $table->enum('jenis', ['nasional', 'cuti_bersama', 'khusus_daerah'])->default('nasional');
            $table->enum('sumber', ['api', 'manual'])->default('manual');
            $table->text('keterangan')->nullable(); // Dasar hukum / no surat edaran
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hari_liburs');
    }
};
