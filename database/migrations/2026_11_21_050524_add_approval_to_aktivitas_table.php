<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            if (! Schema::hasColumn('aktivitas', 'status')) {
                $table->string('status', 20)->default('pending')->index()->after('menit');
            }
            if (! Schema::hasColumn('aktivitas', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            }
            if (! Schema::hasColumn('aktivitas', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            if (Schema::hasColumn('aktivitas', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }
            $cols = array_filter(['status', 'approved_at'], fn ($c) => Schema::hasColumn('aktivitas', $c));
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
