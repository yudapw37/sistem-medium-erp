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
            DB::statement('ALTER TABLE transaction_details MODIFY product_id BIGINT UNSIGNED NULL');
            if (!Schema::hasColumn('transaction_details', 'bundle_id')) {
                DB::statement('ALTER TABLE transaction_details ADD bundle_id BIGINT UNSIGNED NULL AFTER product_id');
            }
        } catch (\Exception $e) {
            // Fallback
        }
    }

    public function down(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn('bundle_id');
        });
    }
};
