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

            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->unique([
                'user_id',
                'tanggal',
            ]);
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
