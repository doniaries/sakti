<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_tugas_user', function (Blueprint $table): void {
            $table->string('status_kehadiran', 30)->default('belum_konfirmasi')->after('dibaca_at');
            $table->text('catatan_kehadiran')->nullable()->after('status_kehadiran');
            $table->timestamp('dikonfirmasi_at')->nullable()->after('catatan_kehadiran');
        });

        Schema::create('laporan_surat_tugas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('surat_tugas_id')->constrained('surat_tugas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('ringkasan_kegiatan');
            $table->string('link_dokumentasi', 500)->nullable();
            $table->json('foto_dokumentasi')->nullable();
            $table->timestamp('dilaporkan_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_surat_tugas');

        Schema::table('surat_tugas_user', function (Blueprint $table): void {
            $table->dropColumn(['status_kehadiran', 'catatan_kehadiran', 'dikonfirmasi_at']);
        });
    }
};
