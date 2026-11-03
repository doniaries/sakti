<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_kegiatan', function (Blueprint $table) {
            if (! Schema::hasColumn('master_kegiatan', 'bidang_id')) {
                $table->foreignId('bidang_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('bidangs')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('master_kegiatan', function (Blueprint $table) {
            if (Schema::hasColumn('master_kegiatan', 'bidang_id')) {
                $table->dropForeign(['bidang_id']);
                $table->dropColumn('bidang_id');
            }
        });
    }
};

