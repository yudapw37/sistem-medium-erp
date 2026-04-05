<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard: skip if table already exists (production safe)
        if (Schema::hasTable('old_order_aktif_details')) {
            return;
        }

        Schema::create('old_order_aktif_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('old_order_aktif_id');
            $table->string('code_order')->nullable();
            $table->string('code_barang')->nullable();
            $table->string('nama_promo')->nullable();
            $table->integer('jumlah')->default(0);
            $table->decimal('harga', 15, 2)->default(0);
            $table->decimal('harga_promo', 15, 2)->default(0);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('old_order_aktif_id')->references('id')->on('old_order_aktif')->onDelete('cascade');
            $table->index('code_order');
            $table->index('code_barang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_order_aktif_details');
    }
};
