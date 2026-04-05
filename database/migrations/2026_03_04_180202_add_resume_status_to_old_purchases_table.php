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
        // Guard: skip if column already exists (production safe)
        if (Schema::hasColumn('old_purchases', 'resume_status')) {
            return;
        }

        Schema::table('old_purchases', function (Blueprint $table) {
            $table->boolean('resume_status')->default(true)->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('old_purchases', function (Blueprint $table) {
            $table->dropColumn('resume_status');
        });
    }
};
