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
        Schema::create('absensi_mobile', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->dateTime('waktu_absensi');

            $table->enum('jenis', [
                'masuk',
                'pulang',
                'dinas_luar',
                'tugas_lapangan',
            ]);

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('akurasi_lokasi', 8, 2)->nullable();

            $table->string('foto')->nullable();

            $table->text('keterangan')->nullable();

            $table->string('perangkat_id')->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'waktu_absensi',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_mobiles');
    }
};
