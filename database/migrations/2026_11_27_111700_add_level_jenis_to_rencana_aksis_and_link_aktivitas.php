<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rencana_aksis', function (Blueprint $table) {
            $table->string('level', 20)->nullable()->index()->after('bidang_id');
            $table->string('jenis', 25)->default('non_trajectory')->after('rencana_aksi');
            $table->string('satuan', 50)->default('kegiatan')->after('jenis');
        });

        Schema::table('aktivitas', function (Blueprint $table) {
            $table->foreignId('rencana_aksi_id')
                ->nullable()
                ->after('bidang_id')
                ->constrained('rencana_aksis')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rencana_aksi_id');
        });

        Schema::table('rencana_aksis', function (Blueprint $table) {
            $table->dropColumn(['level', 'jenis', 'satuan']);
        });
    }
};
