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
        Schema::create('stock_penyesuaians', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['in', 'out'])->comment('in=stok masuk, out=stok keluar');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->date('date');
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'finalized'])->default('draft');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_penyesuaians');
    }
};
