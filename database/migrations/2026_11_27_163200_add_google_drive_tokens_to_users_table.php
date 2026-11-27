<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('google_drive_token')->nullable()->after('google_id');
            $table->text('google_drive_refresh_token')->nullable()->after('google_drive_token');
            $table->timestamp('google_drive_token_expires_at')->nullable()->after('google_drive_refresh_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'google_drive_token',
                'google_drive_refresh_token',
                'google_drive_token_expires_at',
            ]);
        });
    }
};
