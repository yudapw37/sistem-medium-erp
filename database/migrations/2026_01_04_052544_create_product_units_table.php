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
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->decimal('conversion_rate', 10, 4)->default(1); // How many base units
            $table->string('barcode')->nullable()->unique();
            $table->bigInteger('sell_price')->default(0);
            $table->bigInteger('buy_price')->default(0);
            $table->boolean('is_base')->default(false); // True for smallest unit
            $table->boolean('is_default')->default(false); // Default unit for display
            $table->timestamps();
            
            $table->unique(['product_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
