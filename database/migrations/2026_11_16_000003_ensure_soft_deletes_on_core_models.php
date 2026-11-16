<?php

use App\Models\AbsensiHarian;
use App\Models\DetailPengajuanSpj;
use App\Models\Lhk;
use App\Models\LhkPekerjaan;
use App\Models\PengajuanIzin;
use App\Models\PengajuanSpj;
use App\Models\StokBarang;
use App\Models\SuratTugas;
use App\Models\TransaksiBarang;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<class-string>
     */
    protected array $models = [
        User::class,
        AbsensiHarian::class,
        PengajuanIzin::class,
        Lhk::class,
        LhkPekerjaan::class,
        PengajuanSpj::class,
        DetailPengajuanSpj::class,
        SuratTugas::class,
        TransaksiBarang::class,
        StokBarang::class,
    ];

    public function up(): void
    {
        foreach ($this->models as $modelClass) {
            $tableName = (new $modelClass())->getTable();
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->models as $modelClass) {
            $tableName = (new $modelClass())->getTable();
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
