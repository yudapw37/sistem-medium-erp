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
        Schema::create('stock_opname_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_opname_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('system_stock')->default(0);
            $table->integer('physical_stock')->default(0);
            $table->integer('difference')->default(0);
            $table->timestamps();

            $table->foreign('stock_opname_id')->references('id')->on('stock_opnames')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_details');
    }
};
