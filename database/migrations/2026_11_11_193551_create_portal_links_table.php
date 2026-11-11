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
        Schema::create('portal_links', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('deskripsi')->nullable();
            $table->string('icon')->default('fa-solid fa-link');
            $table->string('url')->default('#');
            $table->string('target', 20)->default('_self');
            $table->string('warna', 30)->default('sky');
            $table->string('bentuk', 30)->default('squircle');
            $table->string('kategori', 50)->default('layanan_utama');
            $table->string('badge', 50)->nullable();
            $table->string('lampiran_path')->nullable();
            $table->integer('urutan')->default(0);
            $table->boolean('is_aktif')->default(true);
            $table->string('akses_role', 50)->default('semua');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_links');
    }
};
