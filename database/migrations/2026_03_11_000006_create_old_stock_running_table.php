<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard: skip if table already exists (production safe)
        if (Schema::hasTable('old_stock_running')) {
            return;
        }

        Schema::create('old_stock_running', function (Blueprint $table) {
            $table->id();
            $table->string('code_barang');
            $table->integer('stock_awal')->default(0);
            $table->integer('stock_masuk')->default(0);
            $table->integer('stock_keluar')->default(0);
            $table->integer('stock_saldo')->default(0);
            $table->timestamps();

            $table->unique('code_barang');
            $table->index('code_barang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_stock_running');
    }
};
