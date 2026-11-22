<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_devices')) {
            Schema::create('user_devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('device_id', 128)->index();
                $table->string('device_name', 150);
                $table->string('platform', 32)->default('android');
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('last_login_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['user_id', 'device_id']);
            });
        }

        if (Schema::hasTable('pengaturan_aplikasis') && ! Schema::hasColumn('pengaturan_aplikasis', 'max_devices_per_user')) {
            Schema::table('pengaturan_aplikasis', function (Blueprint $table) {
                $table->unsignedTinyInteger('max_devices_per_user')->default(3)->after('info_update');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');

        if (Schema::hasTable('pengaturan_aplikasis') && Schema::hasColumn('pengaturan_aplikasis', 'max_devices_per_user')) {
            Schema::table('pengaturan_aplikasis', function (Blueprint $table) {
                $table->dropColumn('max_devices_per_user');
            });
        }
    }
};
