<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('old_order', function (Blueprint $table) {
            $table->boolean('resume_status')->default(true)->after('biayaExpedisi');
        });
    }

    public function down(): void
    {
        Schema::table('old_order', function (Blueprint $table) {
            $table->dropColumn('resume_status');
        });
    }
};
