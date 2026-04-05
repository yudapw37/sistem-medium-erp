<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Guard: skip if trigger table doesn't exist (production safe)
        if (!\Illuminate\Support\Facades\Schema::hasTable('old_purchase_details')) {
            return;
        }

        // Drop existing trigger if exists
        DB::unprepared("DROP TRIGGER IF EXISTS trg_after_purchase_detail_insert");

        // Create new trigger that also syncs code_barang from mapping
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

                    -- If mapping exists (code_barang is not null), update the code_barang
                    UPDATE old_purchase_details
                    SET code_barang = (
                        SELECT code_barang FROM old_ms_barang_purchase
                        WHERE nama_barang = NEW.nama COLLATE utf8mb4_unicode_ci
                        LIMIT 1
                    )
                    WHERE id = NEW.id
                    AND EXISTS (
                        SELECT 1 FROM old_ms_barang_purchase
                        WHERE nama_barang = NEW.nama COLLATE utf8mb4_unicode_ci
                        AND code_barang IS NOT NULL
                        AND code_barang != ''
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
