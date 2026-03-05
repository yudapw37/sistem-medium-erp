<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('old_purchase_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('old_purchase_id')->constrained('old_purchases')->cascadeOnDelete();
            $table->string('nama');
            $table->integer('qty')->default(0);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('old_purchase_details');
    }
};
