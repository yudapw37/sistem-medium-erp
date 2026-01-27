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
        Schema::create('sale_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['finance', 'warehouse']);
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['sale_id', 'type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_approvals');
    }
};
