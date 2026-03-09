<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('old_stock_awal', function (Blueprint $table) {
            $table->id();
            $table->string('code_barang');
            $table->integer('qty')->default(0);
            $table->date('tanggal');
            $table->timestamps();

            $table->unique('code_barang');
            $table->index('code_barang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_stock_awal');
    }
};
