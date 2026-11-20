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
                $table->string('device_id', 128)->nullable()->index()->after('remember_token');
            }
            if (! Schema::hasColumn('users', 'device_info')) {
                $table->string('device_info', 255)->nullable()->after('device_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = array_filter(['device_id', 'device_info'], fn ($c) => Schema::hasColumn('users', $c));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
