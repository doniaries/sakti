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
        Schema::create('jadwal_kerjas', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // e.g. "Jadwal Reguler", "Jadwal Ramadhan (Bulan Puasa)"
            $table->string('kode')->unique(); // e.g. 'reguler', 'ramadhan'
            
            // Jam Kerja Senin - Kamis
            $table->time('jam_masuk_senin_kamis')->default('07:30:00');
            $table->time('jam_pulang_senin_kamis')->default('16:00:00');
            
            // Jam Kerja Jumat
            $table->time('jam_masuk_jumat')->default('07:30:00');
            $table->time('jam_pulang_jumat')->default('16:30:00');
            
            $table->unsignedInteger('toleransi_terlambat_menit')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_aktif')->default(true);
            $table->text('keterangan')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_kerjas');
    }
};
