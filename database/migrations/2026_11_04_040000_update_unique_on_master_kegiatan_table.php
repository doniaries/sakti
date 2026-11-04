<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_kegiatan', function (Blueprint $table) {
            $table->dropUnique('master_kegiatan_kode_kegiatan_unique');
            $table->unique(['tahun_anggaran', 'kode_kegiatan'], 'keg_thn_kode_unique');
        });
    }

    public function down(): void
    {
        Schema::table('master_kegiatan', function (Blueprint $table) {
            $table->dropUnique('keg_thn_kode_unique');
            $table->unique('kode_kegiatan', 'master_kegiatan_kode_kegiatan_unique');
        });
    }
};

