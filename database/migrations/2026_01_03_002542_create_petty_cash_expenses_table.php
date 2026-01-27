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
        Schema::create('petty_cash_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('petty_cash_id')->constrained('petty_cashes')->onDelete('cascade');
            $table->date('date');
            $table->string('category'); // listrik, ATK, transport, dll
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->string('receipt_number')->nullable(); // nomor bon/kuitansi
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index('date');
            $table->index('category');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cash_expenses');
    }
};
