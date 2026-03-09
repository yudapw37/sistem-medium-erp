-- Fix old_order_aktif.old_order_id column type
-- Run this on production to fix the sync error

-- 1. Drop foreign key (find the exact name first)
SET @fk_name = (SELECT CONSTRAINT_NAME 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'old_order_aktif' 
    AND COLUMN_NAME = 'old_order_id' 
    AND REFERENCED_TABLE_NAME IS NOT NULL
    LIMIT 1);

SET @sql = IF(@fk_name IS NOT NULL, 
    CONCAT('ALTER TABLE old_order_aktif DROP FOREIGN KEY `', @fk_name, '`'), 
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Change column type
ALTER TABLE old_order_aktif MODIFY old_order_id VARCHAR(255) NULL;

-- 3. Add index if not exists
CREATE INDEX idx_old_order_id ON old_order_aktif (old_order_id);
