<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('old_order_aktif', function (Blueprint $table) {
            $table->id();
            $table->string('old_order_id')->nullable();
            $table->string('code_customer')->nullable();
            $table->string('nama_pengirim')->nullable();
            $table->string('telephone_pengirim')->nullable();
            $table->string('nama_penerima')->nullable();
            $table->string('telephone_penerima')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kab_kota')->nullable();
            $table->integer('total_barang')->default(0);
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->decimal('total_diskon', 15, 2)->default(0);
            $table->decimal('diskon_kode_unik', 15, 2)->default(0);
            $table->decimal('biaya_expedisi', 15, 2)->default(0);
            $table->boolean('is_final')->default(false);
            $table->timestamp('final_at')->nullable();
            $table->timestamps();

            $table->index('old_order_id');
            $table->index('code_customer');
            $table->index('is_final');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_order_aktif');
    }
};
