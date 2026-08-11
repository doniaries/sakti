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
        Schema::create('rencana_aksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->year('tahun')->default(date('Y'));
            $table->text('sasaran_opd')->nullable();
            $table->text('sasaran_program')->nullable();
            $table->text('sasaran_kegiatan')->nullable();
            $table->text('sasaran_sub_kegiatan')->nullable();
            $table->text('sasaran_teknis')->nullable();
            $table->text('indikator')->nullable();
            $table->text('rencana_aksi')->nullable();
            
            // Target per bulan
            $table->integer('target_jan')->default(0);
            $table->integer('target_feb')->default(0);
            $table->integer('target_mar')->default(0);
            $table->integer('target_apr')->default(0);
            $table->integer('target_mei')->default(0);
            $table->integer('target_jun')->default(0);
            $table->integer('target_jul')->default(0);
            $table->integer('target_agu')->default(0);
            $table->integer('target_sep')->default(0);
            $table->integer('target_okt')->default(0);
            $table->integer('target_nov')->default(0);
            $table->integer('target_des')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rencana_aksis');
    }
};
