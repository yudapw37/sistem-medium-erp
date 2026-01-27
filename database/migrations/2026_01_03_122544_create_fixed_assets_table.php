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
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 15, 2);
            $table->integer('useful_life'); // in years
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->enum('depreciation_method', ['straight_line', 'declining_balance'])->default('straight_line');
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->decimal('book_value', 15, 2);
            $table->enum('status', ['active', 'disposed', 'sold'])->default('active');
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_value', 15, 2)->nullable();
            $table->foreignId('asset_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('depreciation_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('accumulated_depreciation_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
