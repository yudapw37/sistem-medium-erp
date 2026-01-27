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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->bigInteger('grand_total');
            $table->enum('status', ['draft', 'finalized'])->default('draft');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
