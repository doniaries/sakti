<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_aplikasis', function (Blueprint $table): void {
            $table->string('portal_theme', 30)->default('default')->after('modul_aktif');
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_aplikasis', function (Blueprint $table): void {
            $table->dropColumn('portal_theme');
        });
    }
};
