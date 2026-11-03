<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_spj', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bidang_id')->constrained('bidangs')->cascadeOnDelete();
            $table->foreignId('kegiatan_id')->constrained('master_kegiatan');
            $table->foreignId('sub_kegiatan_id')->constrained('master_sub_kegiatan');
            $table->date('tanggal_pengajuan');
            $table->decimal('total_nilai_rencana', 15, 2)->default(0);
            $table->decimal('total_nilai_realisasi', 15, 2)->nullable();
            
            $table->string('status', 40)->default('draft')->index();
            
            // Rekanan / Toko
            $table->string('nama_toko')->nullable();
            $table->text('alamat_toko')->nullable();
            $table->string('penerima_nama')->nullable();
            $table->string('penerima_identitas')->nullable(); // NIK/NPWP/Jabatan
            
            // Bukti Unggahan
            $table->string('file_nota_penjual')->nullable();
            $table->string('file_dokumentasi_barang')->nullable();
            
            // Catatan deviasi & revisi
            $table->text('justifikasi_selisih')->nullable();
            $table->text('catatan_revisi')->nullable();
            
            // Pejabat terkait & pencairan
            $table->foreignId('pptk_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ppk_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pa_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('bendahara_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_dicairkan')->nullable();
            $table->string('nomor_bku')->nullable();
            
            $table->timestamps();
        });

        Schema::create('detail_pengajuan_spj', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_spj_id')->constrained('pengajuan_spj')->cascadeOnDelete();
            $table->foreignId('kode_rekening_id')->constrained('kode_rekenings');
            $table->foreignId('master_barang_id')->constrained('master_barangs');
            $table->integer('kuantitas');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            
            // Realisasi
            $table->integer('kuantitas_realisasi')->nullable();
            $table->decimal('harga_realisasi', 15, 2)->nullable();
            $table->decimal('subtotal_realisasi', 15, 2)->nullable();
            
            $table->timestamps();
        });

        Schema::create('verifikasi_spj', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_spj_id')->constrained('pengajuan_spj')->cascadeOnDelete();
            $table->string('tahap', 30); // pptk, ppk, pa, bendahara
            $table->foreignId('user_id')->constrained('users');
            $table->json('checklist_data')->nullable();
            $table->text('catatan')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
        });

        Schema::create('riwayat_status_spj', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_spj_id')->constrained('pengajuan_spj')->cascadeOnDelete();
            $table->string('status_sebelumnya', 40)->nullable();
            $table->string('status_berikutnya', 40);
            $table->foreignId('user_id')->constrained('users');
            $table->string('peran_saat_tindakan', 40)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_status_spj');
        Schema::dropIfExists('verifikasi_spj');
        Schema::dropIfExists('detail_pengajuan_spj');
        Schema::dropIfExists('pengajuan_spj');
    }
};
