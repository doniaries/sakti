<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_id')->constrained('bidangs')->cascadeOnDelete();
            $table->foreignId('master_barang_id')->constrained('master_barangs')->cascadeOnDelete();
            $table->integer('stok_tersedia')->default(0);
            $table->timestamps();

            $table->index('bidang_id');
            $table->index('master_barang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_barangs');
    }
};

