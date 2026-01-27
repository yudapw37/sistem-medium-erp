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
        Schema::create('zero_value_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zero_value_transaction_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('qty');
            $table->decimal('buy_price', 15, 2)->default(0); // Nilai HPP atau 0 untuk bonus
            $table->timestamps();

            $table->foreign('zero_value_transaction_id', 'zvt_details_transaction_id_fk')
                ->references('id')
                ->on('zero_value_transactions')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zero_value_transaction_details');
    }
};
