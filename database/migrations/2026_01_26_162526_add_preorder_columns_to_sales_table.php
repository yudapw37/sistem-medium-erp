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
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('is_preorder')->default(false)->after('invoice');
            $table->enum('preorder_status', ['pending', 'ready', 'completed'])->nullable()->after('is_preorder');
            $table->date('estimated_ready_date')->nullable()->after('preorder_status');
            $table->bigInteger('paid_amount')->default(0)->after('grand_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['is_preorder', 'preorder_status', 'estimated_ready_date', 'paid_amount']);
        });
    }
};
