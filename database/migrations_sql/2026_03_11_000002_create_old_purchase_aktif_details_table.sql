-- Create old_purchase_aktif_details table
CREATE TABLE IF NOT EXISTS old_purchase_aktif_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    old_purchase_aktif_id BIGINT UNSIGNED NOT NULL,
    code_barang VARCHAR(255) NULL,
    nama VARCHAR(255) NULL,
    qty INT DEFAULT 0,
    harga_satuan DECIMAL(15,2) DEFAULT 0,
    total DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (old_purchase_aktif_id) REFERENCES old_purchase_aktif(id) ON DELETE CASCADE,
    INDEX idx_code_barang (code_barang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
