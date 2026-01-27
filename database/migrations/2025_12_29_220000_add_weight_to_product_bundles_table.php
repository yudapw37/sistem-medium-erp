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
        Schema::table('product_bundles', function (Blueprint $table) {
            $table->decimal('weight', 10, 2)->default(0)->after('sell_price')->comment('Total weight in grams');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_bundles', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};
