<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'file_ttd')) {
                $table->string('file_ttd')->nullable()->after('avatar_url');
            }
        });

        Schema::table('pengajuan_spj', function (Blueprint $table) {
            if (! Schema::hasColumn('pengajuan_spj', 'ttd_pptk')) {
                $table->string('ttd_pptk')->nullable()->after('pptk_user_id');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'ttd_ppk')) {
                $table->string('ttd_ppk')->nullable()->after('ppk_user_id');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'ttd_pa')) {
                $table->string('ttd_pa')->nullable()->after('pa_user_id');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'ttd_bendahara')) {
                $table->string('ttd_bendahara')->nullable()->after('bendahara_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_spj', function (Blueprint $table) {
            $cols = array_filter(
                ['ttd_pptk', 'ttd_ppk', 'ttd_pa', 'ttd_bendahara'],
                fn (string $col) => Schema::hasColumn('pengajuan_spj', $col)
            );
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'file_ttd')) {
                $table->dropColumn('file_ttd');
            }
        });
    }
};
