<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_golongans', function (Blueprint $table) {
            if (! Schema::hasColumn('riwayat_golongans', 'jenis_sk')) {
                $table->string('jenis_sk', 25)->nullable()->after('user_id');
            }
        });

        Schema::table('riwayat_sertifikats', function (Blueprint $table) {
            if (! Schema::hasColumn('riwayat_sertifikats', 'jumlah_jam')) {
                $table->unsignedInteger('jumlah_jam')->nullable()->after('tanggal_terbit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_golongans', function (Blueprint $table) {
            if (Schema::hasColumn('riwayat_golongans', 'jenis_sk')) {
                $table->dropColumn('jenis_sk');
            }
        });

        Schema::table('riwayat_sertifikats', function (Blueprint $table) {
            if (Schema::hasColumn('riwayat_sertifikats', 'jumlah_jam')) {
                $table->dropColumn('jumlah_jam');
            }
        });
    }
};
