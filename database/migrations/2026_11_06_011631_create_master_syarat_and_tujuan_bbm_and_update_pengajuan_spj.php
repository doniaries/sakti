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
        // 1. Tabel Master Dokumen Persyaratan Belanja / SPJ
        if (! Schema::hasTable('master_syarat_belanja')) {
            Schema::create('master_syarat_belanja', function (Blueprint $table) {
                $table->id();
                $table->string('jenis_belanja')->default('semua'); // barang_jasa, makan_minum, sppd, bbm, semua
                $table->string('metode_pengadaan')->default('semua'); // e_katalog, non_katalog, semua
                $table->string('nama_syarat');
                $table->text('keterangan')->nullable();
                $table->boolean('is_required')->default(true);
                $table->integer('urutan')->default(1);
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        }

        // 2. Tabel Master Rute & Kuota Liter BBM
        if (! Schema::hasTable('master_tujuan_bbm')) {
            Schema::create('master_tujuan_bbm', function (Blueprint $table) {
                $table->id();
                $table->string('nama_tujuan');
                $table->string('kategori_wilayah')->default('dalam_kabupaten'); // dalam_kabupaten, luar_kabupaten, luar_provinsi
                $table->decimal('jarak_km', 8, 2)->nullable();
                $table->decimal('kuota_liter', 8, 2)->default(0);
                $table->string('jenis_kendaraan')->default('roda_4'); // roda_4, roda_2, bus
                $table->text('keterangan')->nullable();
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        }

        // 3. Tambah field metode pengadaan, jenis belanja, perpajakan, SPPD, dan BBM pada pengajuan_spj
        Schema::table('pengajuan_spj', function (Blueprint $table) {
            if (! Schema::hasColumn('pengajuan_spj', 'metode_pengadaan')) {
                $table->string('metode_pengadaan')->default('non_katalog')->after('nomor_pengajuan'); // e_katalog, non_katalog
            }
            if (! Schema::hasColumn('pengajuan_spj', 'jenis_belanja')) {
                $table->string('jenis_belanja')->default('barang_jasa')->after('metode_pengadaan'); // barang_jasa, makan_minum, sppd, bbm
            }
            if (! Schema::hasColumn('pengajuan_spj', 'pph22_persen')) {
                $table->decimal('pph22_persen', 5, 2)->default(0)->after('ppn_persen');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'nilai_ppn')) {
                $table->decimal('nilai_ppn', 15, 2)->default(0)->after('pph22_persen');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'nilai_pph22')) {
                $table->decimal('nilai_pph22', 15, 2)->default(0)->after('nilai_ppn');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'total_pajak')) {
                $table->decimal('total_pajak', 15, 2)->default(0)->after('nilai_pph22');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'nilai_bersih')) {
                $table->decimal('nilai_bersih', 15, 2)->default(0)->after('total_pajak');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'file_ekatalog_bundel')) {
                $table->string('file_ekatalog_bundel')->nullable()->after('file_dokumentasi_barang');
            }

            // Field SPPD & BBM
            if (! Schema::hasColumn('pengajuan_spj', 'tingkat_perjalanan')) {
                $table->string('tingkat_perjalanan')->nullable()->after('file_ekatalog_bundel'); // dalam_kabupaten, luar_kabupaten, luar_provinsi
            }
            if (! Schema::hasColumn('pengajuan_spj', 'nomor_spt')) {
                $table->string('nomor_spt')->nullable()->after('tingkat_perjalanan');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'tanggal_spt')) {
                $table->date('tanggal_spt')->nullable()->after('nomor_spt');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'maksud_perjalanan')) {
                $table->text('maksud_perjalanan')->nullable()->after('tanggal_spt');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'tanggal_berangkat')) {
                $table->date('tanggal_berangkat')->nullable()->after('maksud_perjalanan');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'tanggal_kembali')) {
                $table->date('tanggal_kembali')->nullable()->after('tanggal_berangkat');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'lama_hari')) {
                $table->integer('lama_hari')->nullable()->after('tanggal_kembali');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'lokasi_tujuan')) {
                $table->string('lokasi_tujuan')->nullable()->after('lama_hari');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'master_tujuan_bbm_id')) {
                $table->foreignId('master_tujuan_bbm_id')->nullable()->after('lokasi_tujuan')->constrained('master_tujuan_bbm')->nullOnDelete();
            }
            if (! Schema::hasColumn('pengajuan_spj', 'jumlah_liter_bbm')) {
                $table->decimal('jumlah_liter_bbm', 8, 2)->nullable()->after('master_tujuan_bbm_id');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'harga_per_liter')) {
                $table->decimal('harga_per_liter', 12, 2)->nullable()->after('jumlah_liter_bbm');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'dokumen_syarat_files')) {
                $table->json('dokumen_syarat_files')->nullable()->after('harga_per_liter');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_spj', function (Blueprint $table) {
            $columnsToDrop = [
                'dokumen_syarat_files',
                'harga_per_liter',
                'jumlah_liter_bbm',
                'master_tujuan_bbm_id',
                'lokasi_tujuan',
                'lama_hari',
                'tanggal_kembali',
                'tanggal_berangkat',
                'maksud_perjalanan',
                'tanggal_spt',
                'nomor_spt',
                'tingkat_perjalanan',
                'file_ekatalog_bundel',
                'nilai_bersih',
                'total_pajak',
                'nilai_pph22',
                'nilai_ppn',
                'pph22_persen',
                'jenis_belanja',
                'metode_pengadaan',
            ];

            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('pengajuan_spj', $col)) {
                    if ($col === 'master_tujuan_bbm_id') {
                        $table->dropForeign(['master_tujuan_bbm_id']);
                    }
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('master_tujuan_bbm');
        Schema::dropIfExists('master_syarat_belanja');
    }
};
