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
            $table->json('penanggung_jawab_ids')->nullable()->after('penanggung_jawab_id');
            $table->json('koordinator_acara_ids')->nullable()->after('koordinator_acara');
        });
    }

    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table): void {
            $table->dropColumn(['penanggung_jawab_ids', 'koordinator_acara_ids']);
        });
    }
};
