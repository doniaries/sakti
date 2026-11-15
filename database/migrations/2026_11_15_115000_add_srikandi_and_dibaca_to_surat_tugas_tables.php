<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->string('file_tte')->nullable()->after('keterangan');
            $table->string('status', 30)->default('draft')->after('file_tte')->index();
            $table->string('sumber', 30)->default('sakti')->after('status');
        });

        Schema::table('surat_tugas_user', function (Blueprint $table) {
            $table->timestamp('dibaca_at')->nullable()->after('peran_tugas')->index();
        });
    }

    public function down(): void
    {
        Schema::table('surat_tugas_user', function (Blueprint $table) {
            $table->dropIndex(['dibaca_at']);
            $table->dropColumn('dibaca_at');
        });

        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['file_tte', 'status', 'sumber']);
        });
    }
};
