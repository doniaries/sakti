<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_sertifikats', function (Blueprint $table) {
            if (! Schema::hasColumn('riwayat_sertifikats', 'tahun')) {
                $table->unsignedSmallInteger('tahun')->nullable()->after('tanggal_terbit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_sertifikats', function (Blueprint $table) {
            if (Schema::hasColumn('riwayat_sertifikats', 'tahun')) {
                $table->dropColumn('tahun');
            }
        });
    }
};
