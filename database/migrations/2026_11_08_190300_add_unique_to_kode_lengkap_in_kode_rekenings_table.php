<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kode_rekenings', function (Blueprint $table) {
            // Drop regular index if exists and add unique index
            $table->dropIndex(['kode_lengkap']);
            $table->unique('kode_lengkap');
        });
    }

    public function down(): void
    {
        Schema::table('kode_rekenings', function (Blueprint $table) {
            $table->dropUnique(['kode_lengkap']);
            $table->index('kode_lengkap');
        });
    }
};

