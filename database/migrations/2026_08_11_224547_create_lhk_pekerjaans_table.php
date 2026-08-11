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
        Schema::create('lhk_pekerjaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lhk_id')->constrained()->cascadeOnDelete();
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('lama_menit')->default(0);
            $table->foreignId('rencana_aksi_id')->nullable()->constrained('rencana_aksis')->nullOnDelete();
            $table->foreignId('aktivitas_id')->nullable()->constrained('aktivitas')->nullOnDelete();
            $table->text('hasil_pekerjaan')->nullable();
            $table->string('bukti_url')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('lhk_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lhk_pekerjaans');
    }
};
