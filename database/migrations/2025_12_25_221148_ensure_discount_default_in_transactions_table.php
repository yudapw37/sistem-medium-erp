<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing NULL discount values to 0
        DB::table('transactions')->whereNull('discount')->update(['discount' => 0]);
        
        // For new installations, ensure discount has default 0
        // Note: Modifying existing column requires doctrine/dbal package
        // This migration ensures data integrity by updating NULL values
        Schema::table('transactions', function (Blueprint $table) {
            // If column doesn't exist, create it with default
            if (!Schema::hasColumn('transactions', 'discount')) {
                $table->bigInteger('discount')->default(0)->after('change');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert as we're just ensuring data integrity
    }
};
