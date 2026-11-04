<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rincian_anggaran_belanja', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun_anggaran')->index();
            $table->foreignId('bidang_id')->constrained('bidangs')->cascadeOnDelete();
            $table->foreignId('sub_kegiatan_id')->constrained('master_sub_kegiatan')->cascadeOnDelete();
            $table->foreignId('kode_rekening_id')->constrained('kode_rekenings')->cascadeOnDelete();
            $table->foreignId('master_barang_id')->nullable()->constrained('master_barangs')->nullOnDelete();

            $table->string('nama_item');
            $table->string('spesifikasi')->nullable();
            $table->string('sumber_dana')->default('Dana Alokasi Umum (DAU)');
            $table->decimal('volume', 12, 2)->default(1);
            $table->string('satuan', 50)->default('Unit');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('pagu_total', 15, 2)->default(0);

            $table->decimal('volume_realisasi', 12, 2)->default(0);
            $table->decimal('nilai_realisasi', 15, 2)->default(0);

            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['tahun_anggaran', 'bidang_id']);
            $table->index(['sub_kegiatan_id', 'kode_rekening_id']);
        });

        // Tambah foreign key rincian_anggaran_belanja_id pada detail_pengajuan_spj
        if (Schema::hasTable('detail_pengajuan_spj')) {
            Schema::table('detail_pengajuan_spj', function (Blueprint $table) {
                $table->foreignId('rincian_anggaran_belanja_id')
                    ->nullable()
                    ->after('pengajuan_spj_id')
                    ->constrained('rincian_anggaran_belanja')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('detail_pengajuan_spj')) {
            Schema::table('detail_pengajuan_spj', function (Blueprint $table) {
                $table->dropForeign(['rincian_anggaran_belanja_id']);
                $table->dropColumn('rincian_anggaran_belanja_id');
            });
        }

        Schema::dropIfExists('rincian_anggaran_belanja');
    }
};

