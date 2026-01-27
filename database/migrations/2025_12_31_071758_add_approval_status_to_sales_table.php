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
            $table->enum('approval_status', ['draft', 'pending_finance', 'pending_warehouse', 'completed', 'rejected'])
                ->default('draft')
                ->after('status');
            $table->text('rejection_notes')->nullable()->after('approval_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'rejection_notes']);
        });
    }
};
