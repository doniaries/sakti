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
        Schema::create('log_absensi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('mesin_absensi_id')
                ->nullable()
                ->constrained('mesin_absensi')
                ->nullOnDelete();

            $table->string('nomor_absensi', 50);

            $table->dateTime('waktu_absensi');

            $table->enum('perangkat', [
                'mesin',
                'mobile',
            ])->default('mesin');

            $table->string('metode_verifikasi')->nullable();

            $table->string('jenis')->nullable();

            // ID log dari mesin, jika tersedia.
            $table->string('kode_log_asli')->nullable();

            // Menyimpan data mentah dari mesin untuk audit.
            $table->json('data_asli')->nullable();

            $table->timestamps();

            $table->index([
                'nomor_absensi',
                'waktu_absensi',
            ]);

            $table->index('waktu_absensi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_absensis');
    }
};
