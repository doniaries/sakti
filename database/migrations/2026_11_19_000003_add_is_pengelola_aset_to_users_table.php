<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_pengelola_aset')->default(false)->after('jabatan_tim_kreatif');
        });

        // Tetapkan IMELDA SANGGETA, A.Md sebagai Pengelola Aset
        DB::table('users')
            ->where('nip', '19751009 200501 2 002')
            ->orWhere('name', 'like', '%IMELDA SANGGETA%')
            ->update(['is_pengelola_aset' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_pengelola_aset');
        });
    }
};
