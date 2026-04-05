<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Guard: skip if index already exists (production safe)
        $indexExists = DB::select("
            SHOW INDEX FROM old_order WHERE Column_name = 'resume_status' AND Key_name != 'PRIMARY'
        ");
        if (!empty($indexExists)) {
            return;
        }

        Schema::table('old_order', function (Blueprint $table) {
            $table->index('resume_status');
        });
    }

    public function down(): void
    {
        Schema::table('old_order', function (Blueprint $table) {
            $table->dropIndex(['resume_status']);
        });
    }
};
