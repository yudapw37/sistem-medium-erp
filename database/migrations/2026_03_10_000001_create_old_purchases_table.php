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
        // Guard: skip if table already exists (production safe)
        if (Schema::hasTable('old_purchases')) {
            return;
        }

        Schema::create('old_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_faktur')->nullable();
            $table->string('supplier');
            $table->date('tanggal_faktur')->nullable();
            $table->decimal('harga_total', 15, 2)->default(0);
            $table->decimal('ppn', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->string('pdf_filename')->nullable();
            $table->integer('pdf_page')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('old_purchases');
    }
};
