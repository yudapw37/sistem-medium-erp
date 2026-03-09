<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('old_purchase_aktif', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('old_purchase_id')->nullable();
            $table->string('nomor_faktur');
            $table->string('supplier')->nullable();
            $table->date('tanggal_faktur')->nullable();
            $table->decimal('harga_total', 15, 2)->default(0);
            $table->decimal('ppn', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->boolean('is_final')->default(false);
            $table->timestamp('final_at')->nullable();
            $table->timestamps();

            $table->foreign('old_purchase_id')->references('id')->on('old_purchases')->onDelete('set null');
            $table->index('nomor_faktur');
            $table->index('is_final');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_purchase_aktif');
    }
};
