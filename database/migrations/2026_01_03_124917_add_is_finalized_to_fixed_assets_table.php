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
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->boolean('is_finalized')->default(false)->after('status');
            $table->foreignId('acquisition_journal_id')->nullable()->after('is_finalized')->constrained('journals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->dropForeign(['acquisition_journal_id']);
            $table->dropColumn(['is_finalized', 'acquisition_journal_id']);
        });
    }
};
