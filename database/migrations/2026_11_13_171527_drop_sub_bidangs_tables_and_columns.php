<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pengajuan_spj') && Schema::hasColumn('pengajuan_spj', 'sub_bidang_id')) {
            Schema::table('pengajuan_spj', function (Blueprint $table) {
                $table->dropConstrainedForeignId('sub_bidang_id');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'sub_bidang_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('sub_bidang_id');
            });
        }

        Schema::dropIfExists('kegiatan_sub_bidang');
        Schema::dropIfExists('sub_bidangs');
    }

    public function down(): void
    {
        // Tidak ada rollback karena fitur Sub Bidang dihapus permanen.
    }
};
