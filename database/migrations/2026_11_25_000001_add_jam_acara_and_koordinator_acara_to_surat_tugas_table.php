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
            $table->string('jam_acara', 100)->nullable()->after('tanggal_selesai');
            $table->string('koordinator_acara', 255)->nullable()->after('penanggung_jawab_id');
        });
    }

    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table): void {
            $table->dropColumn(['jam_acara', 'koordinator_acara']);
        });
    }
};
