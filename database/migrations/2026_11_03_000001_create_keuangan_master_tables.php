<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Satuan Barang
        Schema::create('satuans', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique()->index();
            $table->timestamps();
        });

        // 2. Klasifikasi Kode Rekening
        Schema::create('kode_rekenings', function (Blueprint $table) {
            $table->id();
            $table->string('akun')->index();
            $table->string('kelompok')->index();
            $table->string('jenis')->index();
            $table->string('objek')->index();
            $table->string('rincian_objek')->index();
            $table->string('sub_rincian_objek')->index();
            $table->string('sub_sub_rincian_objek')->index();
            $table->string('kode_lengkap')->index();
            $table->string('uraian')->index();
            $table->timestamps();
        });

        // 3. Master Barang Persediaan
        Schema::create('master_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kode_rekening_id')->nullable()->constrained('kode_rekenings')->restrictOnDelete();
            $table->string('kode_barang')->index();
            $table->string('uraian')->nullable()->index();
            $table->string('nama_barang');
            $table->foreignId('satuan_id')->nullable()->constrained('satuans')->restrictOnDelete();
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kode_rekening_id', 'nama_barang'], 'mbrg_rek_nama_idx');
            $table->index(['nama_barang', 'kode_barang'], 'mbrg_nama_kode_idx');
        });

        // 4. Master Kegiatan
        Schema::create('master_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kegiatan')->unique();
            $table->string('nama_kegiatan');
            $table->timestamps();
        });

        // 5. Master Sub Kegiatan
        Schema::create('master_sub_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('master_kegiatan')->cascadeOnDelete();
            $table->string('kode_sub_kegiatan');
            $table->string('nama_sub_kegiatan');
            $table->timestamps();
        });

        // 6. Stok Barang Persediaan per Bidang
        Schema::create('stok_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_id')->constrained('bidangs')->cascadeOnDelete();
            $table->foreignId('master_barang_id')->constrained('master_barangs')->cascadeOnDelete();
            $table->integer('stok_tersedia')->default(0);
            $table->timestamps();

            $table->index('bidang_id');
            $table->index('master_barang_id');
        });

        // 7. Transaksi Barang Masuk / Keluar
        Schema::create('transaksi_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_id')->constrained('bidangs')->cascadeOnDelete();
            $table->foreignId('master_barang_id')->constrained('master_barangs')->cascadeOnDelete();
            $table->enum('jenis_transaksi', ['masuk', 'keluar']);
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('nilai_barang', 15, 2)->default(0);
            $table->date('tanggal_transaksi');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['bidang_id', 'master_barang_id', 'tanggal_transaksi'], 'trx_brg_tgl_idx');
        });

        // 8. Laporan Rekap Persediaan Bulanan
        Schema::create('laporan_persediaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_id')->constrained('bidangs')->cascadeOnDelete();
            $table->foreignId('master_barang_id')->constrained('master_barangs')->cascadeOnDelete();
            $table->integer('bulan');
            $table->integer('tahun');
            $table->integer('saldo_awal')->default(0);
            $table->integer('jumlah_masuk')->default(0);
            $table->decimal('nilai_masuk', 15, 2)->default(0);
            $table->integer('jumlah_keluar')->default(0);
            $table->decimal('nilai_keluar', 15, 2)->default(0);
            $table->integer('jumlah_expired')->default(0);
            $table->decimal('nilai_expired', 15, 2)->default(0);
            $table->integer('saldo_akhir')->default(0);
            $table->decimal('nilai_akhir', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['bidang_id', 'master_barang_id', 'bulan', 'tahun'], 'lap_brg_bln_thn_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_persediaans');
        Schema::dropIfExists('transaksi_barangs');
        Schema::dropIfExists('stok_barangs');
        Schema::dropIfExists('master_sub_kegiatan');
        Schema::dropIfExists('master_kegiatan');
        Schema::dropIfExists('master_barangs');
        Schema::dropIfExists('kode_rekenings');
        Schema::dropIfExists('satuans');
    }
};
