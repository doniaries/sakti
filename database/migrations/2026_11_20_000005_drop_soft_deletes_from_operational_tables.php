<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['absensi_harian', 'lhks', 'lhk_pekerjaans', 'pengajuan_izins'] as $tableName) {
            if (Schema::hasColumn($tableName, 'deleted_at')) {
                DB::table($tableName)->whereNotNull('deleted_at')->delete();
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropSoftDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['absensi_harian', 'lhks', 'lhk_pekerjaans', 'pengajuan_izins'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->softDeletes();
                });
            }
        }
    }
};
