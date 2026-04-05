<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard: skip if table already exists (production safe)
        if (Schema::hasTable('old_purchase_aktif_details')) {
            return;
        }

        Schema::create('old_purchase_aktif_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('old_purchase_aktif_id');
            $table->string('code_barang')->nullable();
            $table->string('nama')->nullable();
            $table->integer('qty')->default(0);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('old_purchase_aktif_id')->references('id')->on('old_purchase_aktif')->onDelete('cascade');
            $table->index('code_barang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_purchase_aktif_details');
    }
};
