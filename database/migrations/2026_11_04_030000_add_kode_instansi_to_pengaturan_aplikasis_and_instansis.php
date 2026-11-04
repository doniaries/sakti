<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_aplikasis', function (Blueprint $table) {
            $table->string('kode_instansi', 50)->default('3.26.2.19.0.00.01.0000')->after('nama_dinas');
            $table->string('sub_unit_organisasi', 100)->default('-')->after('kode_instansi');
        });

        Schema::table('instansis', function (Blueprint $table) {
            $table->string('kode_instansi', 50)->default('3.26.2.19.0.00.01.0000')->after('nama_dinas');
            $table->string('sub_unit_organisasi', 100)->default('-')->after('kode_instansi');
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_aplikasis', function (Blueprint $table) {
            $table->dropColumn(['kode_instansi', 'sub_unit_organisasi']);
        });

        Schema::table('instansis', function (Blueprint $table) {
            $table->dropColumn(['kode_instansi', 'sub_unit_organisasi']);
        });
    }
};

