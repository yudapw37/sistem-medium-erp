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
        Schema::create('payable_payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->index('payment_date');
            $table->index('purchase_id');
            $table->index('supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payable_payments');
    }
};
