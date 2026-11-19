<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table): void {
            $table->index(['is_visible', 'sort'], 'menus_visible_sort_index');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->index('eselon', 'users_eselon_index');
            $table->index('aktif', 'users_aktif_index');
        });

        Schema::table('surat_tugas', function (Blueprint $table): void {
            $table->index('tanggal_mulai', 'surat_tugas_tanggal_mulai_index');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table): void {
            $table->dropIndex('menus_visible_sort_index');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_eselon_index');
            $table->dropIndex('users_aktif_index');
        });

        Schema::table('surat_tugas', function (Blueprint $table): void {
            $table->dropIndex('surat_tugas_tanggal_mulai_index');
        });
    }
};
