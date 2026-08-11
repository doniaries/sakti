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
        Schema::create('mesin_absensi', function (Blueprint $table) {
            $table->id();

            $table->string('nama');
            $table->string('merek')->nullable();
            $table->string('model')->nullable();

            $table->string('alamat_ip');
            $table->unsignedInteger('port')->default(4370);

            $table->string('nomor_seri')->nullable();

            $table->enum('jenis_koneksi', [
                'tcp_ip',
                'usb',
            ])->default('tcp_ip');

            $table->boolean('aktif')->default(true);

            $table->timestamp('sinkronisasi_terakhir')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesin_absensis');
    }
};
