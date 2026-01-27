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
        Schema::create('zero_value_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['in', 'out']); // in = bonus/hadiah, out = rusak/expired/hibah
            $table->enum('reason', [
                // OUT reasons
                'damaged',    // Barang rusak
                'expired',    // Kadaluarsa
                'donation',   // Hibah/donasi
                'loss',       // Kehilangan
                // IN reasons
                'bonus',      // Bonus dari supplier
                'gift',       // Hadiah
                'promotion',  // Promosi
                'other',      // Lainnya
            ]);
            $table->unsignedBigInteger('warehouse_id');
            $table->date('date');
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'finalized'])->default('draft');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zero_value_transactions');
    }
};
