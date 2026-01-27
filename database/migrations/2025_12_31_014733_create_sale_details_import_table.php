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
        Schema::create('sale_details_import', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_import_id')->constrained('sales_import')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('bundle_id')->nullable()->constrained('product_bundles')->onDelete('set null');
            $table->integer('qty');
            $table->bigInteger('sell_price');
            $table->bigInteger('discount')->default(0);
            $table->decimal('weight', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_details_import');
    }
};
