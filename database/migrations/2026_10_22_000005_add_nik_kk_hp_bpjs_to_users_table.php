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
            $table->string('nik', 20)->nullable()->after('nip')->comment('Nomor Induk Kependudukan (16 digit)');
            $table->string('no_kk', 20)->nullable()->after('nik')->comment('Nomor Kartu Keluarga (16 digit)');
            $table->string('no_hp', 25)->nullable()->after('no_kk')->comment('Nomor Handphone / WhatsApp');
            $table->string('no_bpjs', 30)->nullable()->after('no_hp')->comment('Nomor BPJS Kesehatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nik', 'no_kk', 'no_hp', 'no_bpjs']);
        });
    }
};
