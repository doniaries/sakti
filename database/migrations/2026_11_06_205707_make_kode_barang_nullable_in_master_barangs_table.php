<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_barangs', function (Blueprint $table) {
            $table->string('kode_barang')->nullable()->change();
            $table->decimal('harga_satuan', 15, 2)->default(0)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('master_barangs', function (Blueprint $table) {
            $table->string('kode_barang')->nullable(false)->change();
        });
    }
};
