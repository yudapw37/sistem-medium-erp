<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('old_stock_awal', function (Blueprint $table) {
            $table->boolean('is_synced')->default(false)->after('tanggal');
            $table->timestamp('synced_at')->nullable()->after('is_synced');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('old_stock_awal', function (Blueprint $table) {
            $table->dropColumn(['is_synced', 'synced_at']);
        });
    }
};
