<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_golongans', function (Blueprint $table): void {
            $table->unsignedTinyInteger('masa_kerja_tahun')->nullable()->after('file_sk_golongan');
            $table->unsignedTinyInteger('masa_kerja_bulan')->nullable()->after('masa_kerja_tahun');
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_golongans', function (Blueprint $table): void {
            $table->dropColumn(['masa_kerja_tahun', 'masa_kerja_bulan']);
        });
    }
};
