<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_kegiatan', function (Blueprint $table) {
            if (! Schema::hasColumn('master_kegiatan', 'pagu_anggaran')) {
                $table->decimal('pagu_anggaran', 15, 2)->default(0)->after('nama_kegiatan');
            }
        });

        // Isi pagu_anggaran default untuk kegiatan eksisting yang masih 0
        $kegiatans = DB::table('master_kegiatan')->where('pagu_anggaran', 0)->get();
        $defaultPagus = [75000000, 120000000, 95000000, 150000000, 85000000, 110000000];
        foreach ($kegiatans as $idx => $keg) {
            DB::table('master_kegiatan')
                ->where('id', $keg->id)
                ->update(['pagu_anggaran' => $defaultPagus[$idx % count($defaultPagus)]]);
        }
    }

    public function down(): void
    {
        Schema::table('master_kegiatan', function (Blueprint $table) {
            if (Schema::hasColumn('master_kegiatan', 'pagu_anggaran')) {
                $table->dropColumn('pagu_anggaran');
            }
        });
    }
};

