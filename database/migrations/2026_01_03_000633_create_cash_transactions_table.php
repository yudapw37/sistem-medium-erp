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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->date('date');
            $table->enum('type', ['in', 'out']); // kas masuk / kas keluar
            $table->foreignId('account_id')->constrained('accounts'); // akun lawan (bukan kas)
            $table->foreignId('cash_account_id')->constrained('accounts'); // akun kas/bank
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            
            $table->index('date');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
