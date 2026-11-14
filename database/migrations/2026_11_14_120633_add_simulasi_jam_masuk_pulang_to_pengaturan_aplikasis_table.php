<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_aplikasis', function (Blueprint $table) {
            $table->string('simulasi_jam_masuk', 10)->nullable()->after('simulasi_jam_aktif');
            $table->string('simulasi_jam_pulang', 10)->nullable()->after('simulasi_jam_masuk');
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_aplikasis', function (Blueprint $table) {
            $table->dropColumn(['simulasi_jam_masuk', 'simulasi_jam_pulang']);
        });
    }
};
