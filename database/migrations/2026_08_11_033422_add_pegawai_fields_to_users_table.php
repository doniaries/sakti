<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatans')->nullOnDelete();
            $table->foreignId('bidang_id')->nullable()->constrained('bidangs')->nullOnDelete();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('pangkat_golongan')->nullable();
            $table->string('tmt_pangkat')->nullable();
            $table->string('pendidikan')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('eselon')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['jabatan_id']);
            $table->dropForeign(['bidang_id']);
            $table->dropColumn([
                'jabatan_id',
                'bidang_id',
                'tempat_lahir',
                'tanggal_lahir',
                'pangkat_golongan',
                'tmt_pangkat',
                'pendidikan',
                'jenis_kelamin',
                'eselon',
            ]);
        });
    }
};
