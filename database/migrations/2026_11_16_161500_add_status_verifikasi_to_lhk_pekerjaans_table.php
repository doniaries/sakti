<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('lhk_pekerjaans', 'status_verifikasi')) {
            Schema::table('lhk_pekerjaans', function (Blueprint $table) {
                $table->string('status_verifikasi', 30)->default('menunggu')->index()->after('bukti_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('lhk_pekerjaans', 'status_verifikasi')) {
            Schema::table('lhk_pekerjaans', function (Blueprint $table) {
                $table->dropColumn('status_verifikasi');
            });
        }
    }
};
