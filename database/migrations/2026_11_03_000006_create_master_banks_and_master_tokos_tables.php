<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('master_banks')) {
            Schema::create('master_banks', function (Blueprint $table) {
                $table->id();
                $table->string('kode_bank', 20)->nullable();
                $table->string('nama_bank');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('master_tokos')) {
            Schema::create('master_tokos', function (Blueprint $table) {
                $table->id();
                $table->string('nama_toko');
                $table->string('nama_pemilik')->nullable();
                $table->string('telepon', 50)->nullable();
                $table->text('alamat')->nullable();
                $table->foreignId('master_bank_id')->nullable()->constrained('master_banks')->nullOnDelete();
                $table->string('nomor_rekening', 100)->nullable();
                $table->string('npwp', 50)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('pengajuan_spj', function (Blueprint $table) {
            if (! Schema::hasColumn('pengajuan_spj', 'master_toko_id')) {
                $table->foreignId('master_toko_id')->nullable()->after('status')->constrained('master_tokos')->nullOnDelete();
            }
            if (! Schema::hasColumn('pengajuan_spj', 'master_bank_id')) {
                $table->foreignId('master_bank_id')->nullable()->after('telepon_toko')->constrained('master_banks')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_spj', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_spj', 'master_toko_id')) {
                $table->dropConstrainedForeignId('master_toko_id');
            }
            if (Schema::hasColumn('pengajuan_spj', 'master_bank_id')) {
                $table->dropConstrainedForeignId('master_bank_id');
            }
        });

        Schema::dropIfExists('master_tokos');
        Schema::dropIfExists('master_banks');
    }
};
