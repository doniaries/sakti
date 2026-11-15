<?php

use App\Models\Bidang;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bidangs', function (Blueprint $table): void {
            if (! Schema::hasColumn('bidangs', 'singkatan')) {
                $table->string('singkatan', 30)->nullable()->after('nama');
            }
        });

        Bidang::query()
            ->whereNull('singkatan')
            ->orWhere('singkatan', '')
            ->get()
            ->each(function (Bidang $bidang): void {
                $bidang->updateQuietly([
                    'singkatan' => Bidang::getInisialBidang($bidang->nama ?? $bidang->slug ?? ''),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('bidangs', function (Blueprint $table): void {
            if (Schema::hasColumn('bidangs', 'singkatan')) {
                $table->dropColumn('singkatan');
            }
        });
    }
};
