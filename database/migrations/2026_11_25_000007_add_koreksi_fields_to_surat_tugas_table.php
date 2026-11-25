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
            $table->text('catatan_koreksi')->nullable()->after('status');
            $table->foreignId('dikoreksi_oleh')->nullable()->after('catatan_koreksi')->constrained('users')->nullOnDelete();
            $table->timestamp('dikoreksi_at')->nullable()->after('dikoreksi_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('dikoreksi_oleh');
            $table->dropColumn(['catatan_koreksi', 'dikoreksi_at']);
        });
    }
};
