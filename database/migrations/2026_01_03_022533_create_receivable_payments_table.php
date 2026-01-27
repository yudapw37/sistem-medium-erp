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
        Schema::create('receivable_payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers');
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable(); // cash, transfer, giro, etc
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            
            $table->index('payment_date');
            $table->index('sale_id');
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivable_payments');
    }
};
