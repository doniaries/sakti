<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_pengajuan_spj', function (Blueprint $table) {
            $table->unsignedBigInteger('master_barang_id')->nullable()->change();

            if (!Schema::hasColumn('detail_pengajuan_spj', 'nama_item_belanja')) {
                $table->string('nama_item_belanja')->nullable()->after('master_barang_id');
            }
            if (!Schema::hasColumn('detail_pengajuan_spj', 'satuan')) {
                $table->string('satuan', 50)->nullable()->after('nama_item_belanja');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detail_pengajuan_spj', function (Blueprint $table) {
            if (Schema::hasColumn('detail_pengajuan_spj', 'satuan')) {
                $table->dropColumn('satuan');
            }
            if (Schema::hasColumn('detail_pengajuan_spj', 'nama_item_belanja')) {
                $table->dropColumn('nama_item_belanja');
            }
            $table->unsignedBigInteger('master_barang_id')->nullable(false)->change();
        });
    }
};

