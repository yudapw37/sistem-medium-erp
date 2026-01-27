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
        Schema::table('transactions', function (Blueprint $table) {
            $table->bigInteger('event_discount')->default(0)->after('discount');
            $table->enum('event_discount_type', ['amount', 'percent'])->default('amount')->after('event_discount');
            $table->decimal('event_discount_percent', 5, 2)->nullable()->after('event_discount_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['event_discount', 'event_discount_type', 'event_discount_percent']);
        });
    }
};
