<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_aplikasis', function (Blueprint $table): void {
            if (! Schema::hasColumn('pengaturan_aplikasis', 'app_version')) {
                $table->string('app_version', 50)->nullable()->default('v1.0.0');
            }
            if (! Schema::hasColumn('pengaturan_aplikasis', 'update_notes')) {
                $table->text('update_notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_aplikasis', function (Blueprint $table): void {
            $table->dropColumn(['app_version', 'update_notes']);
        });
    }
};
