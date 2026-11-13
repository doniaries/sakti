<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            if (! Schema::hasColumn('aktivitas', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            }

            // Pastikan foreign key jabatan_id memiliki index terpisah sebelum unique gabungan dihapus
            $table->index('jabatan_id', 'aktivitas_jabatan_id_index');
        });

        Schema::table('aktivitas', function (Blueprint $table) {
            $table->dropUnique('aktivitas_jabatan_bidang_nama_unique');
            $table->text('nama_aktivitas')->change();
            $table->index(['user_id', 'kode'], 'aktivitas_user_kode_index');
        });
    }

    public function down(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            $table->dropIndex('aktivitas_user_kode_index');
            $table->string('nama_aktivitas', 255)->change();
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};

