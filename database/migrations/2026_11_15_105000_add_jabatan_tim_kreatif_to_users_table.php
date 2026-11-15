<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('jabatan_tim_kreatif', 100)
                ->nullable()
                ->after('anggaran_role')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['jabatan_tim_kreatif']);
            $table->dropColumn('jabatan_tim_kreatif');
        });
    }
};
