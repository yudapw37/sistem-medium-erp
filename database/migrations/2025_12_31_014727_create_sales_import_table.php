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
        Schema::create('sales_import', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_log_id')->constrained('import_logs')->onDelete('cascade');
            $table->string('invoice')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->bigInteger('grand_total')->default(0);
            $table->bigInteger('discount')->default(0);
            $table->string('discount_type')->default('amount');
            $table->integer('discount_percent')->nullable();
            $table->bigInteger('event_discount')->default(0);
            $table->string('event_discount_type')->default('amount');
            $table->integer('event_discount_percent')->nullable();
            $table->bigInteger('shipping_cost')->default(0);
            $table->bigInteger('other_cost')->default(0);
            $table->string('shipping_name')->nullable();
            $table->string('shipping_phone')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_phone')->nullable();
            $table->string('payment_type')->default('cash');
            $table->string('shipping_type')->default('pickup');
            $table->enum('status', ['draft', 'finalized'])->default('draft');
            $table->timestamp('finalized_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_import');
    }
};
