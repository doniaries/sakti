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
        Schema::table('jadwal_kerjas', function (Blueprint $table) {
            $table->time('buka_absen_masuk')->default('06:00:00')->after('kode');
            $table->time('tutup_absen_masuk')->default('09:00:00')->after('jam_masuk_jumat');
            $table->time('tutup_absen_pulang')->default('21:00:00')->after('jam_pulang_jumat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_kerjas', function (Blueprint $table) {
            $table->dropColumn(['buka_absen_masuk', 'tutup_absen_masuk', 'tutup_absen_pulang']);
        });
    }
};
