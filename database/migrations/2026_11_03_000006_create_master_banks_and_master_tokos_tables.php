<?php

use App\Enums\NamaBank;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $enumValues = array_map(fn (NamaBank $case) => $case->value, NamaBank::cases());

        if (! Schema::hasTable('master_tokos')) {
            Schema::create('master_tokos', function (Blueprint $table) use ($enumValues) {
                $table->id();
                $table->string('nama_toko');
                $table->string('nama_pemilik')->nullable();
                $table->string('telepon', 50)->nullable();
                $table->text('alamat')->nullable();
                $table->enum('nama_bank', $enumValues)->nullable()->default(NamaBank::BANK_NAGARI->value);
                $table->string('nomor_rekening', 100)->nullable();
                $table->string('npwp', 50)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('pengajuan_spj', function (Blueprint $table) use ($enumValues) {
            if (! Schema::hasColumn('pengajuan_spj', 'master_toko_id')) {
                $table->foreignId('master_toko_id')->nullable()->after('status')->constrained('master_tokos')->nullOnDelete();
            }
            if (! Schema::hasColumn('pengajuan_spj', 'nama_bank')) {
                $table->enum('nama_bank', $enumValues)->nullable()->after('telepon_toko');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_spj', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_spj', 'master_toko_id')) {
                $table->dropConstrainedForeignId('master_toko_id');
            }
            if (Schema::hasColumn('pengajuan_spj', 'nama_bank')) {
                $table->dropColumn('nama_bank');
            }
        });

        Schema::dropIfExists('master_tokos');
    }
};
