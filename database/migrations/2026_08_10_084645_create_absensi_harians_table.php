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
        Schema::create('absensi_harian', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('tanggal');

            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();

            $table->foreignId('log_masuk_id')
                ->nullable()
                ->constrained('log_absensi')
                ->nullOnDelete();

            $table->foreignId('log_pulang_id')
                ->nullable()
                ->constrained('log_absensi')
                ->nullOnDelete();

            $table->enum('status', [
                'hadir',
                'terlambat',
                'tidak_hadir',
                'dinas_luar',
                'cuti',
                'izin',
                'sakit',
                'libur',
            ])->default('hadir');

            $table->boolean('is_absen_mesin')->default(false);
            $table->boolean('is_absen_mobile')->default(false);
            $table->enum('status_apel', ['ikut apel', 'tidak ikut apel'])->nullable();

            $table->decimal('latitude_masuk', 10, 7)->nullable();
            $table->decimal('longitude_masuk', 10, 7)->nullable();
            $table->string('foto_masuk')->nullable();

            $table->decimal('latitude_pulang', 10, 7)->nullable();
            $table->decimal('longitude_pulang', 10, 7)->nullable();
            $table->string('foto_pulang')->nullable();

            $table->string('bukti')->nullable();
            
            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->unique([
                'user_id',
                'tanggal',
            ]);
            $table->index('tanggal');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_harians');
    }
};
