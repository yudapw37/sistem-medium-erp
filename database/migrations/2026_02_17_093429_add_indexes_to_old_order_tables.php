<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('old_order', function (Blueprint $table) {
            $table->index('code_customer');
            $table->index('created_at');
        });

        Schema::table('old_orderdetail', function (Blueprint $table) {
            $table->index('code_order');
            $table->index('code_barang');
        });
    }

    public function down(): void
    {
        Schema::table('old_order', function (Blueprint $table) {
            $table->dropIndex(['code_customer']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('old_orderdetail', function (Blueprint $table) {
            $table->dropIndex(['code_order']);
            $table->dropIndex(['code_barang']);
        });
    }
};
