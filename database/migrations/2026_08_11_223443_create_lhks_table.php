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
        Schema::create('lhks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->foreignId('log_absensi_masuk_id')->nullable()->constrained('log_absensi')->nullOnDelete();
            $table->foreignId('log_absensi_pulang_id')->nullable()->constrained('log_absensi')->nullOnDelete();
            $table->string('status_kehadiran')->default('Hadir');
            $table->decimal('potongan_terlambat', 8, 2)->default(0);
            $table->decimal('potongan_pulang_cepat', 8, 2)->default(0);
            $table->decimal('potongan_kehadiran', 8, 2)->default(0);
            $table->decimal('potongan_kegiatan', 8, 2)->default(0);
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'tanggal']);
            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lhks');
    }
};
