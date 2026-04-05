<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard: skip if columns already exist (production safe)
        if (Schema::hasColumn('old_purchases', 'stock_converted')) {
            return;
        }

        Schema::table('old_purchases', function (Blueprint $table) {
            $table->boolean('stock_converted')->default(false)->after('resume_status');
            $table->timestamp('stock_converted_at')->nullable()->after('stock_converted');
        });
    }

    public function down(): void
    {
        Schema::table('old_purchases', function (Blueprint $table) {
            $table->dropColumn(['stock_converted', 'stock_converted_at']);
        });
    }
};
