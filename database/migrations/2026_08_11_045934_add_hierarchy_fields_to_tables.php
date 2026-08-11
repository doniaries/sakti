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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('atasan_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('bidangs', function (Blueprint $table) {
            $table->foreignId('kepala_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('instansis', function (Blueprint $table) {
            $table->foreignId('kepala_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instansis', function (Blueprint $table) {
            $table->dropForeign(['kepala_id']);
            $table->dropColumn('kepala_id');
        });

        Schema::table('bidangs', function (Blueprint $table) {
            $table->dropForeign(['kepala_id']);
            $table->dropColumn('kepala_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['atasan_id']);
            $table->dropColumn('atasan_id');
        });
    }
};
