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
        Schema::table('pengaturan_aplikasis', function (Blueprint $table) {
            $table->decimal('latitude_kantor', 11, 8)->nullable()->default(-0.69752300)->after('sub_nama');
            $table->decimal('longitude_kantor', 11, 8)->nullable()->default(100.98547200)->after('latitude_kantor');
            $table->integer('radius_meter')->default(150)->after('longitude_kantor');
            $table->string('alamat_kantor')->nullable()->default('Muaro Sijunjung, Sumatera Barat')->after('radius_meter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_aplikasis', function (Blueprint $table) {
            $table->dropColumn(['latitude_kantor', 'longitude_kantor', 'radius_meter', 'alamat_kantor']);
        });
    }
};
