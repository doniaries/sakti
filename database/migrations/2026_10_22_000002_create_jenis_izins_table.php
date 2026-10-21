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
        Schema::create('jenis_izins', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // e.g. "Izin Pribadi", "Sakit", "Cuti Tahunan", "Work From Home (WFH)"
            $table->string('kode')->unique(); // e.g. 'izin', 'sakit', 'cuti_tahunan', 'wfh'
            $table->enum('kategori', ['izin', 'sakit', 'cuti', 'wfh'])->default('izin');
            $table->unsignedInteger('kuota_hari')->nullable(); // null jika tidak ada batas kuota
            $table->boolean('butuh_lampiran')->default(false); // surat dokter/bukti
            $table->boolean('aktif')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_izins');
    }
};
