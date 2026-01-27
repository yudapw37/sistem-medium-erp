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
        Schema::table('sale_details', function (Blueprint $table) {
            $table->bigInteger('discount')->default(0)->after('sell_price')->comment('Discount per item in rupiah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
};
