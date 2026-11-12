<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_spj', function (Blueprint $table) {
            $table->dropForeign('pengajuan_spj_sub_kegiatan_id_foreign');
            $table->foreign('sub_kegiatan_id')
                ->references('id')
                ->on('master_sub_kegiatan')
                ->cascadeOnDelete();

            $table->dropForeign('pengajuan_spj_kegiatan_id_foreign');
            $table->foreign('kegiatan_id')
                ->references('id')
                ->on('master_kegiatan')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_spj', function (Blueprint $table) {
            $table->dropForeign(['sub_kegiatan_id']);
            $table->foreign('sub_kegiatan_id')
                ->references('id')
                ->on('master_sub_kegiatan');

            $table->dropForeign(['kegiatan_id']);
            $table->foreign('kegiatan_id')
                ->references('id')
                ->on('master_kegiatan');
        });
    }
};
