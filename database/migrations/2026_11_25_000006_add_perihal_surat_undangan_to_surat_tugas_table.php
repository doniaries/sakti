<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table): void {
            $table->string('perihal_surat_undangan', 255)->nullable()->after('tanggal_surat_undangan');
        });
    }

    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table): void {
            $table->dropColumn('perihal_surat_undangan');
        });
    }
};
