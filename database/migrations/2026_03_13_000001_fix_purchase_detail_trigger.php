<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Guard: skip if table doesn't exist (production safe)
        if (!\Illuminate\Support\Facades\Schema::hasTable('old_purchase_details')) {
            return;
        }

        // Drop the problematic trigger
        DB::unprepared("DROP TRIGGER IF EXISTS trg_after_purchase_detail_insert");

        // Recreate trigger WITHOUT the UPDATE to old_purchase_details
        // (code_barang sync will be handled in PHP instead)
        DB::unprepared("
            CREATE TRIGGER trg_after_purchase_detail_insert
            AFTER INSERT ON old_purchase_details
            FOR EACH ROW
            BEGIN
                -- Insert new nama_barang to old_ms_barang_purchase if not exists
                IF NEW.nama IS NOT NULL AND NEW.nama != '' THEN
                    INSERT INTO old_ms_barang_purchase (nama_barang, created_at)
                    SELECT NEW.nama, NOW()
                    WHERE NOT EXISTS (
                        SELECT 1 FROM old_ms_barang_purchase
                        WHERE nama_barang = NEW.nama COLLATE utf8mb4_unicode_ci
                    );
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_after_purchase_detail_insert");
    }
};
