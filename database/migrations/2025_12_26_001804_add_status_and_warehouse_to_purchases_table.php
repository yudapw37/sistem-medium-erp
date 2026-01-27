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
        Schema::table('purchases', function (Blueprint $table) {
            $table->enum('status', ['draft', 'finalized'])->default('draft')->after('grand_total');
            $table->timestamp('finalized_at')->nullable()->after('status');
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('finalized_at');
            
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn(['status', 'finalized_at', 'warehouse_id']);
        });
    }
};
