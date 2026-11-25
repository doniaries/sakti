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
            $table->string('dasar_surat_undangan', 500)->nullable()->after('dasar_surat');
            $table->string('bukti_surat_undangan', 500)->nullable()->after('dasar_surat_undangan');
        });
    }

    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table): void {
            $table->dropColumn(['dasar_surat_undangan', 'bukti_surat_undangan']);
        });
    }
};
