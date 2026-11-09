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
        Schema::table('master_sub_kegiatan', function (Blueprint $table) {
            $table->boolean('is_locked')->default(true)->after('nama_sub_kegiatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_sub_kegiatan', function (Blueprint $table) {
            $table->dropColumn('is_locked');
        });
    }
};
