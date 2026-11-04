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
            if (!Schema::hasColumn('pengaturan_aplikasis', 'nama_dinas')) {
                $table->string('nama_dinas')->nullable()->after('sub_nama');
            }
            if (!Schema::hasColumn('pengaturan_aplikasis', 'kepala_id')) {
                $table->foreignId('kepala_id')->nullable()->after('nama_dinas')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('pengaturan_aplikasis', 'nama_kepala_dinas')) {
                $table->string('nama_kepala_dinas')->nullable()->after('kepala_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_aplikasis', function (Blueprint $table) {
            $table->dropForeign(['kepala_id']);
            $table->dropColumn(['nama_dinas', 'kepala_id', 'nama_kepala_dinas']);
        });
    }
};
