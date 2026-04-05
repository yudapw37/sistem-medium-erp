<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard old_order indexes
        if (Schema::hasTable('old_order')) {
            $existingIndexes = collect(DB::select("SHOW INDEX FROM old_order"))->pluck('Key_name')->toArray();

            Schema::table('old_order', function (Blueprint $table) use ($existingIndexes) {
                if (!in_array('old_order_code_customer_index', $existingIndexes)) {
                    $table->index('code_customer');
                }
                if (!in_array('old_order_created_at_index', $existingIndexes)) {
                    $table->index('created_at');
                }
            });
        }

        // Guard old_orderdetail indexes
        if (Schema::hasTable('old_orderdetail')) {
            $existingIndexes = collect(DB::select("SHOW INDEX FROM old_orderdetail"))->pluck('Key_name')->toArray();

            Schema::table('old_orderdetail', function (Blueprint $table) use ($existingIndexes) {
                if (!in_array('old_orderdetail_code_order_index', $existingIndexes)) {
                    $table->index('code_order');
                }
                if (!in_array('old_orderdetail_code_barang_index', $existingIndexes)) {
                    $table->index('code_barang');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('old_order')) {
            Schema::table('old_order', function (Blueprint $table) {
                $table->dropIndex(['code_customer']);
                $table->dropIndex(['created_at']);
            });
        }

        if (Schema::hasTable('old_orderdetail')) {
            Schema::table('old_orderdetail', function (Blueprint $table) {
                $table->dropIndex(['code_order']);
                $table->dropIndex(['code_barang']);
            });
        }
    }
};
