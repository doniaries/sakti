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
        Schema::table('pengaturan_aplikasis', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun_anggaran_aktif')->default((int) date('Y'))->after('versi_aplikasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_aplikasis', function (Blueprint $table) {
            $table->dropColumn('tahun_anggaran_aktif');
        });
    }
};
