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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('jadwal_kerja_id')
                ->nullable()
                ->after('bidang_id')
                ->constrained('jadwal_kerjas')
                ->nullOnDelete();

            $table->enum('status_kerja', ['wfo', 'wfh'])
                ->default('wfo')
                ->after('jadwal_kerja_id');
        });

        Schema::table('absensi_harian', function (Blueprint $table) {
            $table->foreignId('jadwal_kerja_id')
                ->nullable()
                ->after('tanggal')
                ->constrained('jadwal_kerjas')
                ->nullOnDelete();

            $table->enum('jenis_kehadiran', ['wfo', 'wfh', 'dinas_luar'])
                ->default('wfo')
                ->after('status');

            $table->foreignId('pengajuan_izin_id')
                ->nullable()
                ->after('jenis_kehadiran')
                ->constrained('pengajuan_izins')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_harian', function (Blueprint $table) {
            $table->dropForeign(['pengajuan_izin_id']);
            $table->dropColumn('pengajuan_izin_id');
            $table->dropColumn('jenis_kehadiran');
            $table->dropForeign(['jadwal_kerja_id']);
            $table->dropColumn('jadwal_kerja_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['jadwal_kerja_id']);
            $table->dropColumn('jadwal_kerja_id');
            $table->dropColumn('status_kerja');
        });
    }
};
