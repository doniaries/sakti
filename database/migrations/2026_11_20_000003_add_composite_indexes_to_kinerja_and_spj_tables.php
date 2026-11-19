<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rencana_aksis', function (Blueprint $table): void {
            $table->index(['tahun', 'bidang_id', 'jabatan_id'], 'rencana_aksis_tahun_bidang_jabatan_idx');
        });

        Schema::table('lhk_pekerjaans', function (Blueprint $table): void {
            $table->index(['lhk_id', 'status_verifikasi'], 'lhk_pekerjaans_lhk_status_idx');
        });

        Schema::table('aktivitas', function (Blueprint $table): void {
            $table->index(['bidang_id', 'jabatan_id'], 'aktivitas_bidang_jabatan_idx');
        });

        Schema::table('pengajuan_izins', function (Blueprint $table): void {
            $table->index(['user_id', 'status', 'tanggal_mulai'], 'pengajuan_izins_user_status_tgl_idx');
        });

        Schema::table('pengajuan_spj', function (Blueprint $table): void {
            $table->index(['tahun_anggaran', 'bidang_id', 'status'], 'pengajuan_spj_thn_bidang_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('rencana_aksis', function (Blueprint $table): void {
            $table->dropIndex('rencana_aksis_tahun_bidang_jabatan_idx');
        });

        Schema::table('lhk_pekerjaans', function (Blueprint $table): void {
            $table->dropIndex('lhk_pekerjaans_lhk_status_idx');
        });

        Schema::table('aktivitas', function (Blueprint $table): void {
            $table->dropIndex('aktivitas_bidang_jabatan_idx');
        });

        Schema::table('pengajuan_izins', function (Blueprint $table): void {
            $table->dropIndex('pengajuan_izins_user_status_tgl_idx');
        });

        Schema::table('pengajuan_spj', function (Blueprint $table): void {
            $table->dropIndex('pengajuan_spj_thn_bidang_status_idx');
        });
    }
};
