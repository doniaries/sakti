<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kode_rekenings', function (Blueprint $table) {
            $table->string('sub_sub_rincian_objek')->nullable()->default('000')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kode_rekenings', function (Blueprint $table) {
            $table->string('sub_sub_rincian_objek')->nullable(false)->change();
        });
    }
};
