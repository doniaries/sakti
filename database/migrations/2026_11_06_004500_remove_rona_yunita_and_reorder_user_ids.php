<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $targetUser = DB::table('users')->where('name', 'like', '%RONA YUNITA%')->first();
        if (! $targetUser) {
            return;
        }

        $targetId = (int) $targetUser->id;

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // 1. Hapus data milik Rona Yunita
        DB::table('lhks')->where('user_id', $targetId)->delete();
        DB::table('absensi_harian')->where('user_id', $targetId)->delete();
        DB::table('log_absensi')->where('user_id', $targetId)->delete();
        DB::table('model_has_roles')->where('model_id', $targetId)->where('model_type', 'App\\Models\\User')->delete();
        DB::table('model_has_permissions')->where('model_id', $targetId)->where('model_type', 'App\\Models\\User')->delete();
        DB::table('users')->where('id', $targetId)->delete();

        // 2. Turunkan seluruh referensi foreign key yang memiliki nilai > targetId
        DB::statement("UPDATE absensi_harian SET user_id = user_id - 1 WHERE user_id > {$targetId}");
        DB::statement("UPDATE bidangs SET kepala_id = kepala_id - 1 WHERE kepala_id > {$targetId}");
        DB::statement("UPDATE instansis SET kepala_id = kepala_id - 1 WHERE kepala_id > {$targetId}");
        DB::statement("UPDATE lhks SET user_id = user_id - 1 WHERE user_id > {$targetId}");
        DB::statement("UPDATE log_absensi SET user_id = user_id - 1 WHERE user_id > {$targetId}");
        DB::statement("UPDATE master_sub_kegiatan SET jf_id = jf_id - 1 WHERE jf_id > {$targetId}");
        DB::statement("UPDATE model_has_permissions SET model_id = model_id - 1 WHERE model_id > {$targetId} AND model_type = 'App\\\\Models\\\\User'");
        DB::statement("UPDATE model_has_roles SET model_id = model_id - 1 WHERE model_id > {$targetId} AND model_type = 'App\\\\Models\\\\User'");
        DB::statement("UPDATE pengajuan_izins SET user_id = user_id - 1 WHERE user_id > {$targetId}");
        DB::statement("UPDATE pengajuan_spj SET user_id = user_id - 1 WHERE user_id > {$targetId}");
        DB::statement("UPDATE pengajuan_spj SET pptk_user_id = pptk_user_id - 1 WHERE pptk_user_id > {$targetId}");
        DB::statement("UPDATE pengajuan_spj SET ppk_user_id = ppk_user_id - 1 WHERE ppk_user_id > {$targetId}");
        DB::statement("UPDATE pengajuan_spj SET pa_user_id = pa_user_id - 1 WHERE pa_user_id > {$targetId}");
        DB::statement("UPDATE pengajuan_spj SET bendahara_user_id = bendahara_user_id - 1 WHERE bendahara_user_id > {$targetId}");
        DB::statement("UPDATE pengaturan_aplikasis SET kepala_id = kepala_id - 1 WHERE kepala_id > {$targetId}");
        DB::statement("UPDATE reputasis SET user_id = user_id - 1 WHERE user_id > {$targetId}");
        DB::statement("UPDATE reputasis SET penilai_id = penilai_id - 1 WHERE penilai_id > {$targetId}");
        DB::statement("UPDATE riwayat_status_spj SET user_id = user_id - 1 WHERE user_id > {$targetId}");
        DB::statement("UPDATE sessions SET user_id = user_id - 1 WHERE user_id > {$targetId}");
        DB::statement("UPDATE users SET atasan_id = atasan_id - 1 WHERE atasan_id > {$targetId}");
        DB::statement("UPDATE verifikasi_spj SET user_id = user_id - 1 WHERE user_id > {$targetId}");

        // 3. Susun ulang ID dan nomor_absensi di tabel users (urut ASC agar tidak duplikat PK)
        DB::statement("UPDATE users SET nomor_absensi = CONCAT('TEMP_', id) WHERE id > {$targetId}");
        DB::statement("UPDATE users SET id = id - 1 WHERE id > {$targetId} ORDER BY id ASC");
        DB::statement("UPDATE users SET nomor_absensi = CAST(id AS CHAR) WHERE id >= {$targetId}");

        // 4. Reset auto increment tabel users
        $maxId = (int) DB::table('users')->max('id');
        $nextId = $maxId + 1;
        DB::statement("ALTER TABLE users AUTO_INCREMENT = {$nextId}");

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversible tidak diperlukan karena data pegawai yang sudah pindah tidak direstore secara otomatis.
    }
};
