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
        Schema::table('product_warehouses', function (Blueprint $table) {
            $table->integer('booked_stock')->default(0)->after('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_warehouses', function (Blueprint $table) {
            $table->dropColumn('booked_stock');
        });
    }
};
