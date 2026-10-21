<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('gelar_depan')->nullable()->after('name');
            $table->string('gelar_belakang')->nullable()->after('gelar_depan');
            $table->string('agama')->nullable()->after('jenis_kelamin');
            $table->text('alamat')->nullable()->after('agama');
            $table->string('no_telp')->nullable()->after('no_hp');
            $table->string('email_pribadi')->nullable()->after('email');
            $table->string('npwp')->nullable()->after('no_bpjs');
            $table->date('tanggal_npwp')->nullable()->after('npwp');
            $table->string('no_taspen')->nullable()->after('tanggal_npwp');
            $table->string('jenjang_pendidikan')->nullable()->after('pendidikan'); // SMP, SMA/SLTA, D3, S1, S2, S3
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'gelar_depan',
                'gelar_belakang',
                'agama',
                'alamat',
                'no_telp',
                'email_pribadi',
                'npwp',
                'tanggal_npwp',
                'no_taspen',
                'jenjang_pendidikan',
            ]);
        });
    }
};
