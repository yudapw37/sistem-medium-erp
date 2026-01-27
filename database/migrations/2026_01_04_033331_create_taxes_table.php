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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->decimal('rate', 5, 2); // e.g., 11.00 for 11%
            $table->enum('type', ['included', 'excluded'])->default('excluded');
            $table->enum('applies_to', ['sales', 'purchases', 'both'])->default('both');
            $table->foreignId('account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
