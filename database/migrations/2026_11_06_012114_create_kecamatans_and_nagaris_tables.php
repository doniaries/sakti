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
        if (! Schema::hasTable('kecamatans')) {
            Schema::create('kecamatans', function (Blueprint $table) {
                $table->id();
                $table->string('nama_kecamatan');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('nagaris')) {
            Schema::create('nagaris', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kecamatan_id')->constrained('kecamatans')->cascadeOnDelete();
                $table->string('nama_nagari');
                $table->decimal('jarak_km', 8, 2)->nullable();
                $table->decimal('kuota_liter_bbm', 8, 2)->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('master_tujuan_bbm')) {
            Schema::table('master_tujuan_bbm', function (Blueprint $table) {
                if (! Schema::hasColumn('master_tujuan_bbm', 'kecamatan_id')) {
                    $table->foreignId('kecamatan_id')->nullable()->after('nama_tujuan')->constrained('kecamatans')->nullOnDelete();
                }
                if (! Schema::hasColumn('master_tujuan_bbm', 'nagari_id')) {
                    $table->foreignId('nagari_id')->nullable()->after('kecamatan_id')->constrained('nagaris')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('master_tujuan_bbm')) {
            Schema::table('master_tujuan_bbm', function (Blueprint $table) {
                if (Schema::hasColumn('master_tujuan_bbm', 'nagari_id')) {
                    $table->dropForeign(['nagari_id']);
                    $table->dropColumn('nagari_id');
                }
                if (Schema::hasColumn('master_tujuan_bbm', 'kecamatan_id')) {
                    $table->dropForeign(['kecamatan_id']);
                    $table->dropColumn('kecamatan_id');
                }
            });
        }

        Schema::dropIfExists('nagaris');
        Schema::dropIfExists('kecamatans');
    }
};
