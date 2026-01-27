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
        Schema::create('petty_cashes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->date('date');
            $table->enum('type', ['open', 'replenish', 'close']); // buka periode, isi ulang, tutup periode
            $table->decimal('amount', 15, 2);
            $table->decimal('balance', 15, 2)->default(0); // saldo saat ini
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            
            $table->index('date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cashes');
    }
};
