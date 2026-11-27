<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            $table->index(['user_id', 'jabatan_id'], 'aktivitas_user_jabatan_idx');
            $table->index('bidang_id', 'aktivitas_bidang_idx');
            $table->index('kode', 'aktivitas_kode_idx');
        });
    }

    public function down(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            $table->dropIndex('aktivitas_user_jabatan_idx');
            $table->dropIndex('aktivitas_bidang_idx');
            $table->dropIndex('aktivitas_kode_idx');
        });
    }
};
