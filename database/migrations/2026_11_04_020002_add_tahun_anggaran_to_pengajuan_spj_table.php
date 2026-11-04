<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_spj', function (Blueprint $table) {
            if (! Schema::hasColumn('pengajuan_spj', 'tahun_anggaran')) {
                $table->integer('tahun_anggaran')->default((int) date('Y'))->after('sub_kegiatan_id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_spj', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_spj', 'tahun_anggaran')) {
                $table->dropColumn('tahun_anggaran');
            }
        });
    }
};

