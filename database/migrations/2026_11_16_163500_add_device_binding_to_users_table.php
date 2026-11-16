<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'device_id')) {
                $table->string('device_id', 128)->nullable()->index()->after('google_id');
            }
            if (! Schema::hasColumn('users', 'device_name')) {
                $table->string('device_name', 150)->nullable()->after('device_id');
            }
            if (! Schema::hasColumn('users', 'device_bound_at')) {
                $table->timestamp('device_bound_at')->nullable()->after('device_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_values(array_filter([
                Schema::hasColumn('users', 'device_id') ? 'device_id' : null,
                Schema::hasColumn('users', 'device_name') ? 'device_name' : null,
                Schema::hasColumn('users', 'device_bound_at') ? 'device_bound_at' : null,
            ]));
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
