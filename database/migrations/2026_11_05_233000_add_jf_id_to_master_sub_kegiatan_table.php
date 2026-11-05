<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_sub_kegiatan', function (Blueprint $table) {
            $table->foreignId('jf_id')->nullable()->after('kegiatan_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('master_sub_kegiatan', function (Blueprint $table) {
            $table->dropForeign(['jf_id']);
            $table->dropColumn('jf_id');
        });
    }
};
