<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL to make product_id nullable and add bundle_id without constraints
        try {
            DB::statement('ALTER TABLE carts MODIFY product_id BIGINT UNSIGNED NULL');
            if (!Schema::hasColumn('carts', 'bundle_id')) {
                DB::statement('ALTER TABLE carts ADD bundle_id BIGINT UNSIGNED NULL AFTER product_id');
            }
        } catch (\Exception $e) {
            // Fallback for different SQL flavors if needed
        }
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('bundle_id');
            // Reverting product_id to NOT NULL might fail if there are nulls now
        });
    }
};
