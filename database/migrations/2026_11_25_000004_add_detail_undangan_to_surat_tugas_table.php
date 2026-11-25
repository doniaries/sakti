<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table): void {
            $table->string('asal_surat_undangan', 255)->nullable()->after('dasar_surat_undangan');
            $table->date('tanggal_surat_undangan')->nullable()->after('asal_surat_undangan');
            $table->text('ringkasan_isi_undangan')->nullable()->after('tanggal_surat_undangan');
        });
    }

    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table): void {
            $table->dropColumn(['asal_surat_undangan', 'tanggal_surat_undangan', 'ringkasan_isi_undangan']);
        });
    }
};
