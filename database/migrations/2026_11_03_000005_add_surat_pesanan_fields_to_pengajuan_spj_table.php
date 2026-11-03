<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_spj', function (Blueprint $table) {
            if (! Schema::hasColumn('pengajuan_spj', 'telepon_toko')) {
                $table->string('telepon_toko', 50)->nullable()->after('alamat_toko');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'tanggal_batas_pengiriman')) {
                $table->date('tanggal_batas_pengiriman')->nullable()->after('tanggal_pengajuan');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'metode_pembayaran')) {
                $table->string('metode_pembayaran')->nullable()->after('telepon_toko');
            }
            if (! Schema::hasColumn('pengajuan_spj', 'ppn_persen')) {
                $table->decimal('ppn_persen', 5, 2)->default(0)->after('total_nilai_realisasi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_spj', function (Blueprint $table) {
            $cols = array_filter(
                ['telepon_toko', 'tanggal_batas_pengiriman', 'metode_pembayaran', 'ppn_persen'],
                fn (string $col) => Schema::hasColumn('pengajuan_spj', $col)
            );
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
