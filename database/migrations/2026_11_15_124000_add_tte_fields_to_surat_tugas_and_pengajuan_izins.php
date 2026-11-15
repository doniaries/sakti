<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_izins', function (Blueprint $table) {
            $table->timestamp('disetujui_at')->nullable()->after('disetujui_oleh');
            $table->string('hash_dokumen', 64)->nullable()->after('disetujui_at')->index();
        });

        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->timestamp('ditandatangani_at')->nullable()->after('penandatangan_id');
            $table->string('hash_dokumen', 64)->nullable()->after('ditandatangani_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_izins', function (Blueprint $table) {
            $table->dropIndex(['hash_dokumen']);
            $table->dropColumn(['disetujui_at', 'hash_dokumen']);
        });

        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->dropIndex(['hash_dokumen']);
            $table->dropColumn(['ditandatangani_at', 'hash_dokumen']);
        });
    }
};
