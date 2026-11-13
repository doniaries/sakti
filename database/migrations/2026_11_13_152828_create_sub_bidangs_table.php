<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_bidangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_id')->constrained('bidangs')->cascadeOnDelete();
            $table->string('nama');
            $table->foreignId('kepala_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('kegiatan_sub_bidang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_bidang_id')->constrained('sub_bidangs')->cascadeOnDelete();
            $table->foreignId('kegiatan_id')->constrained('master_kegiatan')->cascadeOnDelete();
            $table->unique(['sub_bidang_id', 'kegiatan_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('sub_bidang_id')->nullable()->after('bidang_id')->constrained('sub_bidangs')->nullOnDelete();
        });

        Schema::table('pengajuan_spj', function (Blueprint $table) {
            $table->foreignId('sub_bidang_id')->nullable()->after('bidang_id')->constrained('sub_bidangs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_spj', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sub_bidang_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sub_bidang_id');
        });
        Schema::dropIfExists('kegiatan_sub_bidang');
        Schema::dropIfExists('sub_bidangs');
    }
};
