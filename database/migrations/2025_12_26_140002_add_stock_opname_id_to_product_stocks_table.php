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
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('stock_opname_id')->nullable()->after('purchase_id');
            $table->foreign('stock_opname_id')->references('id')->on('stock_opnames')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropForeign(['stock_opname_id']);
            $table->dropColumn('stock_opname_id');
        });
    }
};
