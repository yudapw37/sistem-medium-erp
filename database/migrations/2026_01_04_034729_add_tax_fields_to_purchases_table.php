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
            $table->decimal('subtotal', 15, 2)->default(0)->after('grand_total');
            $table->foreignId('tax_id')->nullable()->after('subtotal')->constrained('taxes')->onDelete('set null');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_id'); // PPN Masukan
            $table->decimal('tax_rate', 5, 2)->default(0)->after('tax_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['tax_id']);
            $table->dropColumn(['subtotal', 'tax_id', 'tax_amount', 'tax_rate']);
        });
    }
};
