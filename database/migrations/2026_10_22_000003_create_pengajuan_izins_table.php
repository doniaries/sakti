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
        Schema::create('pengajuan_izins', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
                
            $table->foreignId('jenis_izin_id')
                ->constrained('jenis_izins')
                ->cascadeOnDelete();
                
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->unsignedInteger('jumlah_hari')->default(1);
            $table->text('alasan');
            $table->string('lampiran')->nullable(); // bukti / surat sakit
            
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            
            $table->foreignId('disetujui_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
                
            $table->text('catatan_atasan')->nullable();
            
            $table->timestamps();
            
            $table->index('tanggal_mulai');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_izins');
    }
};
