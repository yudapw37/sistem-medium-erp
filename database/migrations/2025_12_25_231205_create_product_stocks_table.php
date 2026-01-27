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
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->enum('type', ['in', 'out']);
            $table->integer('qty');
            $table->integer('previous_stock');
            $table->integer('current_stock');
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};
