<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('master_jenis_belanja')) {
            Schema::create('master_jenis_belanja', function (Blueprint $table) {
                $table->id();
                $table->string('kode')->unique(); // barang_jasa, makan_minum, sppd, bbm, e_katalog, semua, dsb.
                $table->string('nama');
                $table->string('metode_pengadaan')->default('non_katalog'); // non_katalog, e_katalog, semua
                $table->text('deskripsi')->nullable();
                $table->integer('urutan')->default(1);
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('master_syarat_belanja') && ! Schema::hasColumn('master_syarat_belanja', 'master_jenis_belanja_id')) {
            Schema::table('master_syarat_belanja', function (Blueprint $table) {
                $table->foreignId('master_jenis_belanja_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('master_jenis_belanja')
                    ->nullOnDelete();
            });
        }

        // Migrasi data awal dari master_syarat_belanja ke master_jenis_belanja
        $defaultJobs = [
            [
                'kode' => 'barang_jasa',
                'nama' => 'Belanja Barang & Jasa',
                'metode_pengadaan' => 'non_katalog',
                'deskripsi' => 'Pengadaan langsung offline kepada toko rekanan fisik untuk ATK, cetak, komputer, dan perlengkapan kantor.',
                'urutan' => 1,
            ],
            [
                'kode' => 'makan_minum',
                'nama' => 'Makan & Minum Rapat',
                'metode_pengadaan' => 'non_katalog',
                'deskripsi' => 'Jamuan rapat kedinasan offline (snack kotak, konsumsi makan siang, katering rapat dinas).',
                'urutan' => 2,
            ],
            [
                'kode' => 'sppd',
                'nama' => 'Perjalanan Dinas (SPPD)',
                'metode_pengadaan' => 'non_katalog',
                'deskripsi' => 'Perjalanan dinas dalam/luar daerah pejabat dan staf dinas berlandaskan Surat Perintah Tugas (SPT).',
                'urutan' => 3,
            ],
            [
                'kode' => 'bbm',
                'nama' => 'Bahan Bakar Minyak (BBM)',
                'metode_pengadaan' => 'non_katalog',
                'deskripsi' => 'Kebutuhan BBM operasional kendaraan dinas sesuai kuota pagu liter nagari/kabupaten/provinsi.',
                'urutan' => 4,
            ],
            [
                'kode' => 'e_katalog',
                'nama' => 'E-Katalog LKPP (E-Purchasing)',
                'metode_pengadaan' => 'e_katalog',
                'deskripsi' => 'Pengadaan belanja pemerintah melalui katalog elektronik resmi LKPP / E-Procurement.',
                'urutan' => 5,
            ],
            [
                'kode' => 'semua',
                'nama' => 'Dokumen Umum / Lampiran Tambahan',
                'metode_pengadaan' => 'semua',
                'deskripsi' => 'Dokumen pendukung SOP umum yang berlaku lintas jenis belanja SPJ (misal: SPTJM, SK Penugasan, dsb.).',
                'urutan' => 6,
            ],
        ];

        foreach ($defaultJobs as $job) {
            $existing = DB::table('master_jenis_belanja')->where('kode', $job['kode'])->first();
            if (! $existing) {
                $id = DB::table('master_jenis_belanja')->insertGetId([
                    'kode' => $job['kode'],
                    'nama' => $job['nama'],
                    'metode_pengadaan' => $job['metode_pengadaan'],
                    'deskripsi' => $job['deskripsi'],
                    'urutan' => $job['urutan'],
                    'aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $id = $existing->id;
            }

            // Hubungkan syarat dokumen yang cocok
            DB::table('master_syarat_belanja')
                ->where('jenis_belanja', $job['kode'])
                ->whereNull('master_jenis_belanja_id')
                ->update(['master_jenis_belanja_id' => $id]);
        }

        // Tangani data kustom lain di master_syarat_belanja jika ada
        if (Schema::hasTable('master_syarat_belanja')) {
            $otherSyarat = DB::table('master_syarat_belanja')
                ->whereNull('master_jenis_belanja_id')
                ->get();

            foreach ($otherSyarat as $syarat) {
                $slug = $syarat->jenis_belanja ?: 'umum';
                $job = DB::table('master_jenis_belanja')->where('kode', $slug)->first();
                if (! $job) {
                    $nama = ucwords(str_replace('_', ' ', $slug));
                    $jobId = DB::table('master_jenis_belanja')->insertGetId([
                        'kode' => $slug,
                        'nama' => $nama,
                        'metode_pengadaan' => $syarat->metode_pengadaan ?: 'non_katalog',
                        'deskripsi' => "Kelompok belanja {$nama}",
                        'urutan' => 10,
                        'aktif' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $jobId = $job->id;
                }

                DB::table('master_syarat_belanja')
                    ->where('id', $syarat->id)
                    ->update(['master_jenis_belanja_id' => $jobId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('master_syarat_belanja') && Schema::hasColumn('master_syarat_belanja', 'master_jenis_belanja_id')) {
            Schema::table('master_syarat_belanja', function (Blueprint $table) {
                $table->dropForeign(['master_jenis_belanja_id']);
                $table->dropColumn('master_jenis_belanja_id');
            });
        }

        Schema::dropIfExists('master_jenis_belanja');
    }
};
