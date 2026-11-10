<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('master_jenis_belanja') && Schema::hasTable('master_syarat_belanja')) {
            $jobs = DB::table('master_jenis_belanja')->get();
            foreach ($jobs as $job) {
                if ($job->kode === 'e_katalog') {
                    DB::table('master_syarat_belanja')
                        ->where('metode_pengadaan', 'e_katalog')
                        ->whereNull('master_jenis_belanja_id')
                        ->update(['master_jenis_belanja_id' => $job->id]);
                } else {
                    DB::table('master_syarat_belanja')
                        ->where('jenis_belanja', $job->kode)
                        ->whereNull('master_jenis_belanja_id')
                        ->update(['master_jenis_belanja_id' => $job->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert linking
    }
};
