<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only run if table already exists (production fix)
        // If table was created fresh with correct types, this is a no-op
        if (!Schema::hasTable('old_order_aktif')) {
            return;
        }

        // Check current column type
        $column = DB::selectOne("
            SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'old_order_aktif' 
            AND COLUMN_NAME = 'old_order_id'
        ");

        // Only alter if still integer type
        if ($column && str_contains(strtolower($column->DATA_TYPE), 'int')) {
            // Drop any foreign keys on old_order_id
            $fks = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'old_order_aktif' 
                AND COLUMN_NAME = 'old_order_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            foreach ($fks as $fk) {
                DB::statement("ALTER TABLE old_order_aktif DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }

            // Change column type
            DB::statement('ALTER TABLE old_order_aktif MODIFY old_order_id VARCHAR(255) NULL');

            // Add index if not exists
            $indexExists = DB::select("
                SHOW INDEX FROM old_order_aktif WHERE Column_name = 'old_order_id' AND Key_name != 'PRIMARY'
            ");
            if (empty($indexExists)) {
                DB::statement('ALTER TABLE old_order_aktif ADD INDEX idx_old_order_id (old_order_id)');
            }
        }
    }

    public function down(): void
    {
        // No rollback - this is a data-preserving fix
    }
};
